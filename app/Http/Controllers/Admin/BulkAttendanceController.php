<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Employee;
use App\Models\EmployeeAttendanceMaster;
use App\Models\EmployeeAttendanceDetail;
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
            ->select('id', 'first_name', 'last_name', 'designation', 'job_role', 'emp_id')
            ->orderBy('id')
            ->get()
            ->map(function($employee) {
                // Combine first_name and last_name into name field
                $fullName = trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''));
                return [
                    'id' => $employee->id,
                    'name' => $fullName ?: 'No Name Provided',
                    'emp_id' => $employee->emp_id ?? 'N/A',
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

        // Get existing attendance data if any
        $existingAttendance = $this->getExistingAttendance($siteId, $month, $userType);

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

            // Create or update attendance master record
            $master = EmployeeAttendanceMaster::updateOrCreate([
                'site_id' => $siteId,
                'month' => $month,
                'user_type' => $userType
            ], [
                'created_by' => Auth::id(),
                'tenant_id' => tenant('id') ?? 1 // Fallback to 1 if tenant context is not available
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
                return $items->keyBy('date')->map(function ($item) {
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
                    'days' => $details->keyBy('date')->map(function ($detail) {
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
     * Delete attendance record
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $master = EmployeeAttendanceMaster::findOrFail($id);
            
            // Delete all related details first
            EmployeeAttendanceDetail::where('attendance_master_id', $id)->delete();
            
            // Delete master record
            $master->delete();

            DB::commit();

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
}
