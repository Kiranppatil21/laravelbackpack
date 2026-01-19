<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Employee;
use App\Models\EmployeeAttendanceMaster;
use App\Models\EmployeeAttendanceDetail;
use App\Models\EmployeeAttendanceAudit;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BulkAttendanceController extends Controller
{
    /**
     * Display the bulk attendance form
     */
    public function index()
    {
        $clients = Client::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->select('id', 'name')->orderBy('name')->get();
        
        // Use same designation options as employee form
        $userTypes = [
            'Security Guard' => 'Security Guard',
            'Supervisor' => 'Supervisor',
            'Manager' => 'Manager',
            'Officer' => 'Officer',
            'Executive' => 'Executive',
            'Watchman' => 'Watchman',
            'Bouncer' => 'Bouncer',
        ];

        \Log::info('Available user types', ['user_types' => array_keys($userTypes)]);

        // Define shift options
        $shifts = [
            '1' => 'First Shift',
            '2' => 'Second Shift',
            '3' => 'Third Shift'
        ];

        return view('admin.bulk-attendance.index', compact('clients', 'userTypes', 'shifts'));
    }

    /**
     * Search and load employees and calendar for attendance
     */
    public function search(Request $request)
    {
        \Log::info('=== BULK ATTENDANCE SEARCH STARTED ===', [
            'all_request_data' => $request->all(),
            'request_method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'user_agent' => $request->header('User-Agent')
        ]);

        $validator = Validator::make($request->all(), [
            'site_id' => 'required|exists:clients,id',
            'user_type' => 'required|string',
            'month' => 'required|date_format:Y-m',
            'shifts' => 'array'
        ]);

        if ($validator->fails()) {
            \Log::error('VALIDATION FAILED', [
                'errors' => $validator->errors(),
                'input' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        \Log::info('VALIDATION PASSED', ['validated_data' => $validator->validated()]);

        $siteId = $request->site_id;
        $userType = $request->user_type;
        $month = $request->month;
        $selectedShifts = $request->shifts ?? ['1', '2', '3'];

        // Debug logging
        \Log::info('Bulk Attendance Search Debug', [
            'site_id' => $siteId,
            'user_type' => $userType,
            'month' => $month,
            'shifts' => $selectedShifts
        ]);

        // First, let's see what employees exist for this client
        $clientEmployees = Employee::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->where('client_id', $siteId)->get(['id', 'first_name', 'last_name', 'designation', 'client_id']);
        \Log::info('All employees for client', [
            'client_id' => $siteId,
            'employees' => $clientEmployees->map(function($emp) {
                return [
                    'id' => $emp->id,
                    'name' => $emp->first_name . ' ' . $emp->last_name,
                    'designation' => $emp->designation,
                    'client_id' => $emp->client_id
                ];
            })->toArray()
        ]);

        // Get employees assigned to this client with the specific designation
        $employees = Employee::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->where('client_id', $siteId)
            ->where('designation', $userType)
            ->select('id', 'first_name', 'last_name', 'designation', 'job_role')
            ->orderBy('id')
            ->get()
            ->map(function($employee) {
                // Combine first_name and last_name into name field
                $fullName = trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''));
                return [
                    'id' => $employee->id,
                    'name' => $fullName ?: 'No Name Provided',
                    'emp_id' => $employee->id, // Use ID as emp_id since emp_id column doesn't exist
                    'designation' => $employee->designation ?? 'No Designation',
                    'job_role' => $employee->job_role ?? 'N/A',
                    'first_name' => $employee->first_name,
                    'last_name' => $employee->last_name
                ];
            });

        \Log::info('Employees filtered by designation', [
            'client_id' => $siteId,
            'designation' => $userType,
            'count' => $employees->count(),
            'employee_details' => $employees->map(function($emp) {
                return [
                    'id' => $emp['id'],
                    'name' => $emp['name'],
                    'emp_id' => $emp['emp_id'],
                    'designation' => $emp['designation']
                ];
            })->toArray()
        ]);
        
        // Search completed successfully
        \Log::info('Employee search with designation filter completed', [
            'client_id' => $siteId,
            'designation' => $userType,
            'employees_found' => $employees->count(),
            'search_criteria' => 'Employees for client ' . $siteId . ' with designation: ' . $userType
        ]);

        // Generate calendar for the month
        $calendar = $this->generateCalendar($month);

        // Get public holidays and employee leaves for the month
        $publicHolidays = $this->getPublicHolidays($month);
        $employeeLeaves = $this->getEmployeeLeaves($siteId, $month, $employees->pluck('id')->toArray());

        // Get existing attendance data if any
        $existingAttendance = $this->getExistingAttendance($siteId, $month, $userType);

        // Master record (for status/approval state)
        $masterMeta = EmployeeAttendanceMaster::where([
            'site_id' => $siteId,
            'month' => $month,
            'user_type' => $userType,
        ])->first(['id','status','approved_by','approved_at']);

        // Get site name
        $site = Client::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)->find($siteId);

        // Add debug information
        $debugInfo = [
            'total_employees_in_db' => Employee::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)->count(),
            'employees_for_client' => Employee::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)->where('client_id', $siteId)->count(),
            'employees_with_designation' => Employee::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)->where('client_id', $siteId)->where('designation', $userType)->count(),
            'all_designations_for_client' => Employee::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)->where('client_id', $siteId)->whereNotNull('designation')->distinct()->pluck('designation')->toArray()
        ];

        \Log::info('Search completed', array_merge($debugInfo, [
            'employees_returned' => $employees->count(),
            'existing_attendance_count' => count($existingAttendance)
        ]));

        return response()->json([
            'success' => true,
            'data' => [
                'employees' => $employees,
                'calendar' => $calendar,
                'site' => $site,
                'month' => $month,
                'user_type' => $userType,
                'shifts' => $selectedShifts,
                'existing_attendance' => $existingAttendance,
                'master' => $masterMeta,
                'public_holidays' => $publicHolidays,
                'employee_leaves' => $employeeLeaves,
                'debug_info' => $debugInfo
            ],
            'debug' => [
                'employees_count' => $employees->count(),
                'raw_employees' => $employees->toArray(),
                'query_params' => [
                    'site_id' => $siteId,
                    'user_type' => $userType,
                    'month' => $month
                ]
            ]
        ]);
    }

    /**
     * Store bulk attendance data
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'site_id' => 'required|exists:clients,id',
            'user_type' => 'required|string',
            'month' => 'required|date_format:Y-m',
            'attendance' => 'required|array',
            'attendance.*.*.shift' => 'sometimes|in:1,2,3',
            'attendance.*.*.is_ot' => 'sometimes|in:0,1'  // Accept 0 or 1
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $siteId = $request->site_id;
            $userType = $request->user_type;
            $month = $request->month;
            $attendanceData = $request->attendance;

            // Prevent editing locked records
            $existingMaster = EmployeeAttendanceMaster::where([
                'site_id' => $siteId,
                'month' => $month,
                'user_type' => $userType
            ])->first();

            if ($existingMaster && ($existingMaster->status === EmployeeAttendanceMaster::STATUS_LOCKED)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance is locked and cannot be edited.'
                ], 403);
            }

            // Create or update attendance master record
            $master = EmployeeAttendanceMaster::updateOrCreate([
                'site_id' => $siteId,
                'month' => $month,
                'user_type' => $userType
            ], [
                'created_by' => Auth::id(),
                'tenant_id' => tenant('id') ?? 1, // Fallback to 1 if tenant context is not available
                // Reset to draft on modification unless already submitted/approved/locked
                'status' => $existingMaster && $existingMaster->status ? $existingMaster->status : EmployeeAttendanceMaster::STATUS_DRAFT,
            ]);

            // Clear existing attendance details for this master record
            EmployeeAttendanceDetail::where('attendance_master_id', $master->id)->delete();

            $totalRecords = 0;

            // Process each employee's attendance
            foreach ($attendanceData as $employeeId => $days) {
                // Validate employee exists
                $employee = Employee::find($employeeId);
                if (!$employee) {
                    throw new \Exception("Employee with ID {$employeeId} not found");
                }
                
                foreach ($days as $date => $attendance) {
                    if (isset($attendance['shift']) && $attendance['shift']) {
                        // Validate date format
                        if (!Carbon::createFromFormat('Y-m-d', $date)) {
                            throw new \Exception("Invalid date format: {$date}");
                        }
                        
                        $detail = EmployeeAttendanceDetail::create([
                            'attendance_master_id' => $master->id,
                            'employee_id' => $employeeId,
                            'site_id' => $siteId,
                            'date' => $date,
                            'shift' => $attendance['shift'],
                            'is_present' => true,
                            'is_ot' => (bool)($attendance['is_ot'] ?? 0)  // Convert 1/0 to boolean
                        ]);
                        
                        if (!$detail) {
                            throw new \Exception("Failed to create attendance detail for employee {$employeeId} on {$date}");
                        }
                        
                        $totalRecords++;
                    }
                }
            }

            // Audit log
            $this->logAudit($master->id, $existingMaster ? 'update' : 'create', null, [
                'total_records' => $totalRecords,
                'site_id' => $siteId,
                'month' => $month,
                'user_type' => $userType,
            ], null, $request);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Attendance saved successfully! {$totalRecords} records processed.",
                'master_id' => $master->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error saving attendance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit attendance for approval (draft -> submitted)
     */
    public function submit($id, Request $request)
    {
        $master = EmployeeAttendanceMaster::findOrFail($id);
        if ($master->status !== EmployeeAttendanceMaster::STATUS_DRAFT) {
            return response()->json(['success' => false, 'message' => 'Only draft records can be submitted.'], 422);
        }
        $before = $master->only(['status']);
        $master->status = EmployeeAttendanceMaster::STATUS_SUBMITTED;
        $master->save();
        $this->logAudit($master->id, 'submit', $before, $master->only(['status']), null, $request);
        return response()->json(['success' => true, 'message' => 'Submitted for approval.', 'status' => $master->status]);
    }

    /**
     * Approve attendance (submitted -> approved)
     */
    public function approve($id, Request $request)
    {
        $user = Auth::user();
        if (!$user || ! $user->hasAnyRole(['Super Admin','Agency Owner','HR'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $master = EmployeeAttendanceMaster::findOrFail($id);
        if ($master->status !== EmployeeAttendanceMaster::STATUS_SUBMITTED) {
            return response()->json(['success' => false, 'message' => 'Only submitted records can be approved.'], 422);
        }
        $before = $master->only(['status','approved_by','approved_at']);
        $master->status = EmployeeAttendanceMaster::STATUS_APPROVED;
        $master->approved_by = $user->id;
        $master->approved_at = now();
        $master->save();
        $this->logAudit($master->id, 'approve', $before, $master->only(['status','approved_by','approved_at']), null, $request);
        return response()->json(['success' => true, 'message' => 'Attendance approved.', 'status' => $master->status]);
    }

    /**
     * Lock attendance (approved -> locked)
     */
    public function lock($id, Request $request)
    {
        $user = Auth::user();
        if (!$user || ! $user->hasAnyRole(['Super Admin','Agency Owner','HR'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }
        $master = EmployeeAttendanceMaster::findOrFail($id);
        if ($master->status !== EmployeeAttendanceMaster::STATUS_APPROVED) {
            return response()->json(['success' => false, 'message' => 'Only approved records can be locked.'], 422);
        }
        $before = $master->only(['status']);
        $master->status = EmployeeAttendanceMaster::STATUS_LOCKED;
        $master->save();
        $this->logAudit($master->id, 'lock', $before, $master->only(['status']), null, $request);
        return response()->json(['success' => true, 'message' => 'Attendance locked.', 'status' => $master->status]);
    }

    /**
     * Write an audit record
     */
    private function logAudit(int $masterId, string $action, $before = null, $after = null, $detailId = null, ?Request $request = null): void
    {
        try {
            EmployeeAttendanceAudit::create([
                'attendance_master_id' => $masterId,
                'attendance_detail_id' => $detailId,
                'site_id' => null,
                'changed_by' => Auth::id(),
                'action' => $action,
                'before' => $before,
                'after' => $after,
                'ip' => $request ? $request->ip() : request()->ip(),
                'user_agent' => $request ? $request->header('User-Agent') : request()->header('User-Agent'),
            ]);
        } catch (\Throwable $e) {
            \Log::warning('Failed to write attendance audit: '.$e->getMessage());
        }
    }

    /**
     * Generate calendar array for a given month
     */
    private function generateCalendar(string $month): array
    {
        $date = Carbon::createFromFormat('Y-m', $month);
        $daysInMonth = $date->daysInMonth;
        $calendar = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $currentDate = $date->copy()->day($day);
            $calendar[] = [
                'day' => $day,
                'date' => $currentDate->format('Y-m-d'),
                'day_name' => $currentDate->format('D'),
                'is_weekend' => $currentDate->isWeekend(),
                'is_sunday' => $currentDate->isSunday(),
                'is_saturday' => $currentDate->isSaturday()
            ];
        }

        return $calendar;
    }

    /**
     * Get existing attendance data for month
     */
    private function getExistingAttendance(int $siteId, string $month, string $userType): array
    {
        $master = EmployeeAttendanceMaster::where([
            'site_id' => $siteId,
            'month' => $month,
            'user_type' => $userType
        ])->first();

        if (!$master) {
            return [];
        }

        $details = EmployeeAttendanceDetail::where('attendance_master_id', $master->id)
            ->get()
            ->groupBy('employee_id')
            ->map(function ($items) {
                return $items->keyBy(function($item){
                    return $item->date instanceof \Carbon\Carbon
                        ? $item->date->format('Y-m-d')
                        : (is_string($item->date) ? substr($item->date, 0, 10) : $item->date);
                })->map(function ($item) {
                    return [
                        'shift' => $item->shift,
                        'is_ot' => $item->is_ot
                    ];
                });
            })
            ->toArray();

        // Transform data to match frontend expectations
        $transformedData = [];
        foreach ($details as $employeeId => $dates) {
            foreach ($dates as $date => $attendance) {
                $key = "{$employeeId}-{$date}";
                $transformedData[$key] = [
                    'shift_1' => ($attendance['shift'] == '1'),
                    'shift_2' => ($attendance['shift'] == '2'), 
                    'shift_3' => ($attendance['shift'] == '3'),
                    'ot' => (bool)$attendance['is_ot']
                ];
            }
        }

        return $transformedData;
    }

    /**
     * View existing attendance records
     */
    public function view()
    {
        $attendanceRecords = EmployeeAttendanceMaster::with(['site', 'creator'])
            ->orderBy('month', 'desc')
            ->paginate(15);

        return view('admin.bulk-attendance.view', compact('attendanceRecords'));
    }

    /**
     * Show details of specific attendance record
     */
    public function show($id)
    {
        $master = EmployeeAttendanceMaster::with(['site', 'details.employee'])
            ->findOrFail($id);

        $calendar = $this->generateCalendar($master->month);

        // Group attendance details by employee
        $attendanceByEmployee = $master->details()
            ->with('employee')
            ->get()
            ->groupBy('employee_id')
            ->map(function ($details) {
                return [
                    'employee' => $details->first()->employee,
                    'days' => $details->keyBy(function($detail){
                        return $detail->date instanceof \Carbon\Carbon
                            ? $detail->date->format('Y-m-d')
                            : (is_string($detail->date) ? substr($detail->date, 0, 10) : $detail->date);
                    })->map(function ($detail) {
                        return [
                            'shift' => $detail->shift,
                            'is_ot' => $detail->is_ot
                        ];
                    })
                ];
            });

        return view('admin.bulk-attendance.show', compact('master', 'calendar', 'attendanceByEmployee'));
    }

    /**
     * Return latest audit trail entries for a master record (JSON for AJAX)
     */
    public function audits($id)
    {
        $master = EmployeeAttendanceMaster::findOrFail($id);
        $auditsRaw = EmployeeAttendanceAudit::where('attendance_master_id', $master->id)
            ->orderByDesc('id')
            ->take(50)
            ->get(['id','action','changed_by','created_at']);
        // Map user names
        $userIds = $auditsRaw->pluck('changed_by')->filter()->unique();
        $users = \App\Models\User::whereIn('id', $userIds)->get(['id','name']);
        $nameMap = $users->pluck('name','id');
        $audits = $auditsRaw->map(function($a) use ($nameMap){
            return [
                'id' => $a->id,
                'action' => $a->action,
                'changed_by' => $a->changed_by,
                'changed_by_name' => $a->changed_by ? ($nameMap[$a->changed_by] ?? 'User #'.$a->changed_by) : null,
                'created_at' => $a->created_at->format('Y-m-d H:i:s')
            ];
        });
        return response()->json([
            'success' => true,
            'master_id' => $master->id,
            'audits' => $audits,
            'count' => $audits->count()
        ]);
    }

    /**
     * Presence summary matrix (employees x days with presence metadata)
     */
    public function summary($id)
    {
        $master = EmployeeAttendanceMaster::with('details')->findOrFail($id);
        $calendar = $this->generateCalendar($master->month);
        $calendarDates = collect($calendar)->pluck('date');

        // Group details by employee & date (keyed by Y-m-d)
        $details = $master->details()->get()->groupBy('employee_id')->map(function($items){
            return $items->keyBy(function($item){
                return $item->date instanceof \Carbon\Carbon
                    ? $item->date->format('Y-m-d')
                    : (is_string($item->date) ? substr($item->date, 0, 10) : $item->date);
            });
        });
        // Fetch all employees for this site & designation (user_type) so that absent employees are included
        $employees = Employee::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->where('client_id', $master->site_id)
            ->where('designation', $master->user_type)
            ->get(['id','first_name','last_name','position']);

        $matrix = [];
        foreach ($employees as $emp) {
            $row = [
                'employee_id' => $emp->id,
                'name' => trim($emp->first_name.' '.$emp->last_name),
                'position' => $emp->position,
                'days' => []
            ];
            foreach ($calendar as $day) {
                $employeeDetails = $details->get($emp->id);
                $att = $employeeDetails ? $employeeDetails->get($day['date']) : null;
                $row['days'][] = [
                    'date' => $day['date'],
                    'day' => $day['day'],
                    'weekday' => $day['day_name'],
                    'is_weekend' => $day['is_weekend'],
                    'present' => (bool)$att,
                    'shift' => $att ? $att->shift : null,
                    'is_ot' => $att ? (bool)$att->is_ot : false,
                ];
            }
            $matrix[] = $row;
        }

        return response()->json([
            'success' => true,
            'master_id' => $master->id,
            'month' => $master->month,
            'user_type' => $master->user_type,
            'site_id' => $master->site_id,
            'matrix' => $matrix,
            'total_employees' => count($matrix),
            'days_in_month' => count($calendar)
        ]);
    }

    /**
     * Export presence/absence CSV for a master record
     */
    public function exportCsv($id)
    {
        $master = EmployeeAttendanceMaster::with(['details','site'])->findOrFail($id);
        $calendar = $this->generateCalendar($master->month);
        $details = $master->details()->get()->groupBy('employee_id')->map(function($items){
            return $items->keyBy(function($item){
                return $item->date instanceof \Carbon\Carbon
                    ? $item->date->format('Y-m-d')
                    : (is_string($item->date) ? substr($item->date, 0, 10) : $item->date);
            });
        });
        // Include all employees for the site + designation (even if no attendance details yet)
        $employees = Employee::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->where('client_id', $master->site_id)
            ->where('designation', $master->user_type)
            ->get(['id','first_name','last_name','position']);

        $fileName = 'attendance_presence_'.$master->id.'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"'
        ];

        $filter = request()->query('filter'); // null|present|absent

        $callback = function() use ($employees, $details, $calendar, $master, $filter) {
            $out = fopen('php://output', 'w');
            // Header row
            fputcsv($out, ['attendance_ref','site_name','month','user_type','employee_id','employee_name','position','date','day','weekday','is_weekend','present','shift','is_ot']);
            foreach ($employees as $emp) {
                foreach ($calendar as $day) {
                    $employeeDetails = $details->get($emp->id);
                    $att = $employeeDetails ? $employeeDetails->get($day['date']) : null;
                    $present = $att ? 1 : 0;
                    if ($filter === 'present' && !$present) { continue; }
                    if ($filter === 'absent' && $present) { continue; }
                    fputcsv($out, [
                        'ATT-'.$master->id,
                        $master->site?->name ?? 'Unknown Site',
                        $master->month,
                        $master->user_type,
                        $emp->id,
                        trim($emp->first_name.' '.$emp->last_name),
                        $emp->position,
                        $day['date'],
                        $day['day'],
                        $day['day_name'],
                        $day['is_weekend'] ? 1 : 0,
                        $present,
                        $att ? $att->shift : null,
                        $att ? ($att->is_ot ? 1 : 0) : 0,
                    ]);
                }
            }
            fclose($out);
        };
        return response()->streamDownload($callback, $fileName, $headers);
    }

    /**
     * Delete attendance record
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $master = EmployeeAttendanceMaster::findOrFail($id);
            if ($master->status === EmployeeAttendanceMaster::STATUS_LOCKED) {
                return redirect()->back()->with('error', 'Locked attendance cannot be deleted.');
            }
            
            // Delete all related details first
            EmployeeAttendanceDetail::where('attendance_master_id', $id)->delete();
            
            // Delete master record
            $before = $master->toArray();
            $master->delete();

            DB::commit();
            $this->logAudit($id, 'delete', $before, null, null, request());
            return redirect()->back()->with('success', 'Attendance record deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error deleting attendance record: ' . $e->getMessage());
        }
    }

    /**
     * Delete all attendance records for a specific site, month, and year
     */
    public function deleteBulk(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'site_id' => 'required|exists:clients,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2099',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed: ' . implode(', ', $validator->errors()->all())
            ], 422);
        }

        try {
            DB::beginTransaction();

            $site_id = $request->site_id;
            $month = $request->month;
            $year = $request->year;

            // Find all attendance master records for this site/month/year
            $masterRecords = EmployeeAttendanceMaster::where('site_id', $site_id)
                ->where('month', $month)
                ->where('year', $year)
                ->get();

            if ($masterRecords->isEmpty()) {
                return response()->json([
                    'message' => 'No attendance records found for the specified criteria.'
                ], 404);
            }

            $deletedCount = 0;
            foreach ($masterRecords as $master) {
                // Delete all related details first
                EmployeeAttendanceDetail::where('attendance_master_id', $master->id)->delete();
                
                // Delete master record
                $master->delete();
                $deletedCount++;
            }

            DB::commit();

            return response()->json([
                'message' => "Successfully deleted {$deletedCount} attendance record(s) for the specified month.",
                'deleted_count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Bulk delete attendance error', [
                'site_id' => $request->site_id,
                'month' => $request->month,
                'year' => $request->year,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'message' => 'Error deleting attendance records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get public holidays for a given month
     */
    private function getPublicHolidays($month)
    {
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = Carbon::parse($month . '-01')->endOfMonth();

        return \App\Models\Leave::where('leave_type', 'public_holiday')
            ->where('status', 'approved')
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function($q) use ($startDate, $endDate) {
                          $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                      });
            })
            ->get(['start_date', 'end_date', 'reason'])
            ->flatMap(function($holiday) {
                $dates = [];
                $current = Carbon::parse($holiday->start_date);
                $end = Carbon::parse($holiday->end_date);
                
                while ($current <= $end) {
                    $dates[$current->format('Y-m-d')] = [
                        'date' => $current->format('Y-m-d'),
                        'reason' => $holiday->reason,
                        'type' => 'public_holiday'
                    ];
                    $current->addDay();
                }
                return $dates;
            })
            ->values()
            ->toArray();
    }

    /**
     * Get employee leaves for a given month
     */
    private function getEmployeeLeaves($siteId, $month, $employeeIds)
    {
        if (empty($employeeIds)) {
            return [];
        }

        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = Carbon::parse($month . '-01')->endOfMonth();

        return \App\Models\Leave::whereIn('employee_id', $employeeIds)
            ->whereIn('status', ['approved', 'pending'])
            ->where('leave_type', '!=', 'public_holiday')
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function($q) use ($startDate, $endDate) {
                          $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                      });
            })
            ->get(['employee_id', 'start_date', 'end_date', 'leave_type', 'status', 'is_half_day'])
            ->flatMap(function($leave) {
                $dates = [];
                $current = Carbon::parse($leave->start_date);
                $end = Carbon::parse($leave->end_date);
                
                while ($current <= $end) {
                    $key = $leave->employee_id . '_' . $current->format('Y-m-d');
                    $dates[$key] = [
                        'employee_id' => $leave->employee_id,
                        'date' => $current->format('Y-m-d'),
                        'leave_type' => $leave->leave_type,
                        'status' => $leave->status,
                        'is_half_day' => $leave->is_half_day,
                    ];
                    $current->addDay();
                }
                return $dates;
            })
            ->values()
            ->toArray();
    }
}
