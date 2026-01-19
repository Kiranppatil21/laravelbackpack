<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Attendance;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Payroll;
use App\Models\StatutoryReport;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedException;

class DashboardController extends Controller
{
    public function index()
    {
        // Check if user is authenticated
        if (!backpack_auth()->check()) {
            return redirect()->route('backpack.auth.login');
        }
        
        $user = backpack_user();
        
        // Additional check for user existence
        if (!$user) {
            backpack_auth()->logout();
            return redirect()->route('backpack.auth.login')->with('error', 'Authentication required');
        }
        
        // Get user-specific dashboard data based on role
        $dashboardData = $this->getDashboardData($user);
        
        return view('admin.dashboard', $dashboardData);
    }

    /**
     * Recent agencies for Super Admin dashboard (central DB if available)
     */
    protected function getRecentAgencies()
    {
        try {
            if (!Schema::hasTable('agencies')) return [];

            return Agency::latest('created_at')->take(5)->get()->map(function ($a) {
                return [
                    'id' => $a->id,
                    'name' => $a->name,
                    'contact' => $a->contact_name ?? $a->contact_email ?? null,
                    'status' => $a->status ?? null,
                    'created_at' => $a->created_at,
                    'link' => backpack_url('agency/'.$a->id.'/show'),
                ];
            })->toArray();
        } catch (\Throwable $e) {
            \Log::warning('Failed to load recent agencies: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Refresh CSRF token for AJAX requests
     */
    public function refreshCsrf(Request $request)
    {
        if ($request->ajax()) {
            return response()->json([
                'token' => csrf_token()
            ]);
        }
        
        return response()->json(['error' => 'Invalid request'], 400);
    }

    protected function getDashboardData($user)
    {
        $data = [
            'user' => $user,
            'current_month' => now()->format('F Y'),
            'dashboard_widgets' => [],
            'charts' => [],
            'recent_activity' => [],
            'quick_actions' => [],
        ];

        // Check if user has roles (Spatie package might not be installed)
        if (!method_exists($user, 'hasRole')) {
            // Fallback for users without role system
            return array_merge($data, $this->getBasicMetrics($user));
        }

        try {
            // Super Admin gets comprehensive system-wide metrics
            if ($user->hasRole('Super Admin')) {
                $data = array_merge($data, $this->getSuperAdminMetrics());
            }
            // Agency Owner gets agency-specific metrics
            elseif ($user->hasRole('Agency Owner')) {
                $data = array_merge($data, $this->getAgencyOwnerMetrics($user));
            }
            // HR gets employee and attendance focused metrics
            elseif ($user->hasRole('HR')) {
                $data = array_merge($data, $this->getHRMetrics($user));
            }
            // Client gets client-specific metrics
            elseif ($user->hasRole('Client')) {
                $data = array_merge($data, $this->getClientMetrics($user));
            }
            // Other roles get basic metrics
            else {
                $data = array_merge($data, $this->getBasicMetrics($user));
            }
        } catch (\Exception $e) {
            // Fallback if role checking fails
            \Log::warning('Dashboard role checking failed: ' . $e->getMessage());
            $data = array_merge($data, $this->getBasicMetrics($user));
        }

        // Ensure all required keys are arrays
        $data['dashboard_widgets'] = is_array($data['dashboard_widgets'] ?? null) ? $data['dashboard_widgets'] : [];
        $data['charts'] = is_array($data['charts'] ?? null) ? $data['charts'] : [];
        $data['recent_activity'] = is_array($data['recent_activity'] ?? null) ? $data['recent_activity'] : [];
        $data['quick_actions'] = is_array($data['quick_actions'] ?? null) ? $data['quick_actions'] : [];

        return $data;
    }

    protected function getSuperAdminMetrics()
    {
        try {
            $currentMonth = now()->startOfMonth();
            $lastMonth = now()->subMonth()->startOfMonth();
            
            return [
                'dashboard_widgets' => [
                    [
                        'title' => 'Total Tenants',
                        'value' => class_exists('App\Models\Tenant') ? \App\Models\Tenant::count() : 0,
                        'icon' => 'fas fa-building',
                        'color' => 'primary',
                        'trend' => $this->calculateTenantTrend(),
                    ],
                    [
                        'title' => 'Active Agencies',
                        'value' => $this->getTotalAcrossAllTenants('agencies'),
                        'icon' => 'fas fa-shield-alt',
                        'color' => 'success',
                        'link' => backpack_url('agency'),
                    ],
                    [
                        'title' => 'Total Employees',
                        'value' => $this->getTotalAcrossAllTenants('employees'),
                        'icon' => 'fas fa-users',
                        'color' => 'info',
                        'link' => backpack_url('employee'),
                    ],
                    [
                        'title' => 'Monthly Revenue',
                        'value' => '$' . number_format($this->getTotalRevenueThisMonth(), 2),
                        'icon' => 'fas fa-dollar-sign',
                        'color' => 'warning',
                        'trend' => $this->calculateRevenueTrend(),
                    ],
                ],
                'charts' => [
                    'tenant_growth' => $this->getTenantGrowthData(),
                    'revenue_by_month' => $this->getRevenueByMonthData(),
                ],
                'recent_activity' => $this->getSystemActivity(),
                'quick_actions' => [
                    [
                        'title' => 'Manage Users',
                        'url' => backpack_url('user'),
                        'icon' => 'fas fa-users',
                        'color' => 'success',
                    ],
                    [
                        'title' => 'View Agencies',
                        'url' => backpack_url('agency'),
                        'icon' => 'fas fa-shield-alt',
                        'color' => 'primary',
                    ],
                    [
                        'title' => 'Create Agency',
                        'url' => backpack_url('agency/create'),
                        'icon' => 'fas fa-plus',
                        'color' => 'success',
                    ],
                    [
                        'title' => 'System Reports',
                        'url' => '#',
                        'icon' => 'fas fa-chart-bar',
                        'color' => 'info',
                    ],
                ],
                // Recent agencies (central listing) to show on Super Admin dashboard
                'recent_agencies' => $this->getRecentAgencies(),
                'recent_tenants' => $this->getRecentTenants(),
                'alerts' => $this->getSystemAlerts(),
                // Additional agency lead counts for Super Admin quick view
                'agency_leads_counts' => $this->getNewAgencyCounts(),
            ];
        } catch (\Exception $e) {
            \Log::warning('Super admin metrics failed: ' . $e->getMessage());
            return $this->getBasicMetrics(backpack_user());
        }
    }

    protected function getAgencyOwnerMetrics($user)
    {
        try {
            $tenantId = $user->tenant_id ?? null;
            $currentMonth = now()->startOfMonth();
            
            return [
                'dashboard_widgets' => [
                    [
                        'title' => 'Total Clients',
                        'value' => $tenantId && class_exists('App\Models\Client') ? 
                            \App\Models\Client::where('tenant_id', $tenantId)->count() : 0,
                        'icon' => 'fas fa-handshake',
                        'color' => 'primary',
                        'link' => backpack_url('client'),
                    ],
                    [
                        'title' => 'Active Employees',
                        'value' => $tenantId && class_exists('App\Models\Employee') ? 
                            \App\Models\Employee::where('tenant_id', $tenantId)->count() : 0,
                        'icon' => 'fas fa-users',
                        'color' => 'success',
                        'link' => backpack_url('employee'),
                    ],
                    [
                        'title' => 'This Month Revenue',
                        'value' => '$' . number_format($this->getMonthlyRevenue($tenantId), 2),
                        'icon' => 'fas fa-chart-line',
                        'color' => 'info',
                        'link' => backpack_url('invoice'),
                    ],
                    [
                        'title' => 'Attendance Rate',
                        'value' => number_format($this->getAttendanceRate($tenantId), 1) . '%',
                        'icon' => 'fas fa-clock',
                        'color' => 'warning',
                        'link' => backpack_url('attendance'),
                    ],
                ],
                'charts' => [
                    'employee_distribution' => $this->getEmployeeDistributionData($tenantId),
                    'revenue_trend' => $this->getRevenueTrendData($tenantId),
                ],
                'recent_activity' => $this->getAgencyActivity($tenantId),
                'quick_actions' => [
                    [
                        'title' => 'Add New Client',
                        'url' => backpack_url('client/create'),
                        'icon' => 'fas fa-plus',
                        'color' => 'success',
                    ],
                    [
                        'title' => 'Register Employee',
                        'url' => backpack_url('employee/create'),
                        'icon' => 'fas fa-user-plus',
                        'color' => 'primary',
                    ],
                    [
                        'title' => 'Generate Payroll',
                        'url' => backpack_url('payroll/create'),
                        'icon' => 'fas fa-money-check',
                        'color' => 'info',
                    ],
                ],
            ];
        } catch (\Exception $e) {
            \Log::warning('Agency owner metrics failed: ' . $e->getMessage());
            return $this->getBasicMetrics($user);
        }
    }

    /**
     * Return recent tenants for Super Admin dashboard
     */
    protected function getRecentTenants()
    {
        try {
            if (!class_exists('\App\\Models\\Tenant') || !Schema::hasTable('tenants')) return [];

            return \App\Models\Tenant::latest('created_at')->take(5)->get()->map(function ($t) {
                return [
                    'id' => $t->id,
                    'name' => $t->name ?? ($t->uuid ?? $t->id),
                    'uuid' => $t->uuid ?? null,
                    'created_at' => $t->created_at,
                    'link' => backpack_url('tenant/'.$t->id.'/show'),
                ];
            })->toArray();
        } catch (\Throwable $e) {
            \Log::warning('Failed to load recent tenants: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch system alerts if available, otherwise return empty array
     */
    protected function getSystemAlerts()
    {
        try {
            if (!Schema::hasTable('system_alerts')) return [];

            $alerts = DB::table('system_alerts')->latest('created_at')->take(5)->get();
            return $alerts->map(function ($a) {
                return [
                    'id' => $a->id,
                    'title' => $a->title ?? 'Alert',
                    'severity' => $a->severity ?? 'info',
                    'created_at' => $a->created_at,
                ];
            })->toArray();
        } catch (\Throwable $e) {
            \Log::warning('Failed to load system alerts: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Handle quick-create Agency POST from Super Admin modal
     */
    public function quickCreateAgency(Request $request)
    {
        if (!backpack_user() || !backpack_user()->hasRole('Super Admin')) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'in:active,inactive'],
            'tenant_uuid' => ['nullable', 'string'],
        ]);

        try {
            $create = [
                'name' => $data['name'],
                'contact_name' => $data['contact_name'] ?? null,
                'contact_email' => $data['contact_email'] ?? null,
                'contact_phone' => $data['contact_phone'] ?? null,
                'status' => $data['status'] ?? 'active',
            ];

            // Optionally attach tenant info if provided and tenants table exists
            if (!empty($data['tenant_uuid']) && class_exists('\App\\Models\\Tenant')) {
                $tenant = \App\Models\Tenant::where('uuid', $data['tenant_uuid'])->first();
                if ($tenant) {
                    $create['tenant_id'] = $tenant->id ?? null;
                }
            }

            $agency = Agency::create($create);

            return response()->json(['success' => true, 'agency' => [
                'id' => $agency->id,
                'name' => $agency->name,
                'contact' => $agency->contact_name ?? $agency->contact_email ?? null,
                'status' => $agency->status ?? null,
                'link' => backpack_url('agency/'.$agency->id.'/show'),
            ]]);
        } catch (\Throwable $e) {
            \Log::warning('Failed to quick-create agency: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to create agency'], 500);
        }
    }

    protected function getHRMetrics($user)
    {
        $tenantId = $user->tenant_id;
        
        return [
            'dashboard_widgets' => [
                [
                    'title' => 'Total Employees',
                    'value' => Employee::where('tenant_id', $tenantId)->count(),
                    'icon' => 'fas fa-users',
                    'color' => 'primary',
                    'link' => backpack_url('employee'),
                ],
                [
                    'title' => 'Present Today',
                    'value' => $this->getTodayPresentCount($tenantId),
                    'icon' => 'fas fa-user-check',
                    'color' => 'success',
                ],
                [
                    'title' => 'Pending KYC',
                    'value' => Employee::where('tenant_id', $tenantId)
                        ->whereNull('kyc_completed_at')->count(),
                    'icon' => 'fas fa-id-card',
                    'color' => 'warning',
                ],
                [
                    'title' => 'This Month Payroll',
                    'value' => '$' . number_format($this->getMonthlyPayrollCost($tenantId), 2),
                    'icon' => 'fas fa-money-bill-wave',
                    'color' => 'info',
                    'link' => backpack_url('payroll'),
                ],
            ],
            'charts' => [
                'attendance_trends' => $this->getAttendanceTrendsData($tenantId),
                'employee_by_client' => $this->getEmployeeByClientData($tenantId),
            ],
            'recent_activity' => $this->getHRActivity($tenantId),
            'quick_actions' => [
                [
                    'title' => 'Mark Attendance',
                    'url' => backpack_url('attendance/create'),
                    'icon' => 'fas fa-clock',
                    'color' => 'success',
                ],
                [
                    'title' => 'Process Payroll',
                    'url' => backpack_url('payroll/create'),
                    'icon' => 'fas fa-calculator',
                    'color' => 'primary',
                ],
                [
                    'title' => 'Employee Reports',
                    'url' => '#',
                    'icon' => 'fas fa-chart-bar',
                    'color' => 'info',
                ],
            ],
        ];
    }

    protected function getClientMetrics($user)
    {
        // Assuming clients can see their assigned employees and related data
        $clientId = $user->client_id ?? null;
        
        return [
            'dashboard_widgets' => [
                [
                    'title' => 'Assigned Guards',
                    'value' => Employee::where('client_id', $clientId)->count(),
                    'icon' => 'fas fa-shield-alt',
                    'color' => 'primary',
                ],
                [
                    'title' => 'On Duty Today',
                    'value' => $this->getClientTodayPresentCount($clientId),
                    'icon' => 'fas fa-user-check',
                    'color' => 'success',
                ],
                [
                    'title' => 'This Month Hours',
                    'value' => number_format($this->getClientMonthlyHours($clientId), 1),
                    'icon' => 'fas fa-clock',
                    'color' => 'info',
                ],
                [
                    'title' => 'Service Rating',
                    'value' => $this->getClientServiceRating($clientId),
                    'icon' => 'fas fa-star',
                    'color' => 'warning',
                ],
            ],
            'charts' => [
                'guard_attendance' => $this->getClientAttendanceData($clientId),
            ],
            'recent_activity' => $this->getClientActivity($clientId),
            'quick_actions' => [
                [
                    'title' => 'View Guards',
                    'url' => backpack_url('employee'),
                    'icon' => 'fas fa-users',
                    'color' => 'primary',
                ],
                [
                    'title' => 'Attendance Report',
                    'url' => '#',
                    'icon' => 'fas fa-file-alt',
                    'color' => 'info',
                ],
            ],
        ];
    }

    protected function getBasicMetrics($user)
    {
        return [
            'dashboard_widgets' => [
                [
                    'title' => 'Welcome',
                    'value' => $user->name,
                    'icon' => 'fas fa-user',
                    'color' => 'primary',
                ],
            ],
            'quick_actions' => [
                [
                    'title' => 'My Profile',
                    'url' => backpack_url('auth/account/info'),
                    'icon' => 'fas fa-user-cog',
                    'color' => 'primary',
                ],
            ],
        ];
    }

    // Helper methods for data calculations
    protected function calculateTrend($current, $previous)
    {
        if ($previous == 0) return $current > 0 ? '+100%' : '0%';
        $change = (($current - $previous) / $previous) * 100;
        return ($change >= 0 ? '+' : '') . number_format($change, 1) . '%';
    }

    protected function getTotalAcrossAllTenants($table)
    {
        // For multi-tenant setup, count from main tenant database
        try {
            switch ($table) {
                case 'agencies':
                    return Schema::hasTable('agencies') ? Agency::count() : 0;
                case 'employees':
                    return Schema::hasTable('employees') ? Employee::count() : 0;
                case 'clients':
                    return Schema::hasTable('clients') ? Client::count() : 0;
                default:
                    return 0;
            }
        } catch (\Exception $e) {
            \Log::warning('Error counting ' . $table . ': ' . $e->getMessage());
            return 0;
        }
    }

    protected function getTotalRevenueThisMonth()
    {
        if (!Schema::hasTable('invoices')) return 0;
        
        return Invoice::whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('total');
    }

    protected function getMonthlyRevenue($tenantId)
    {
        if (!Schema::hasTable('invoices')) return 0;
        
        return Invoice::where('tenant_id', $tenantId)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('total');
    }

    protected function getAttendanceRate($tenantId)
    {
        if (!Schema::hasTable('attendances')) return 0;
        
        $totalDays = now()->daysInMonth;
        $workingDays = $totalDays - (now()->weekendsInMonth() * 2);
        $employeeCount = Employee::where('tenant_id', $tenantId)->count();
        
        if ($employeeCount == 0) return 0;
        
        $expectedAttendance = $workingDays * $employeeCount;
        $actualAttendance = Attendance::where('tenant_id', $tenantId)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->where('status', 'present')
            ->count();
            
        return $expectedAttendance > 0 ? ($actualAttendance / $expectedAttendance) * 100 : 0;
    }

    protected function getTodayPresentCount($tenantId)
    {
        if (!Schema::hasTable('attendances')) return 0;
        
        return Attendance::where('tenant_id', $tenantId)
            ->whereDate('date', today())
            ->where('status', 'present')
            ->count();
    }

    protected function getMonthlyPayrollCost($tenantId)
    {
        if (!Schema::hasTable('payrolls')) return 0;
        
        return Payroll::where('tenant_id', $tenantId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('gross');
    }

    protected function getClientTodayPresentCount($clientId)
    {
        if (!Schema::hasTable('attendances') || !$clientId) return 0;
        
        return Attendance::whereHas('employee', function($query) use ($clientId) {
            $query->where('client_id', $clientId);
        })
        ->whereDate('date', today())
        ->where('status', 'present')
        ->count();
    }

    protected function getClientMonthlyHours($clientId)
    {
        if (!Schema::hasTable('attendances') || !$clientId) return 0;
        
        $attendances = Attendance::whereHas('employee', function($query) use ($clientId) {
            $query->where('client_id', $clientId);
        })
        ->whereMonth('date', now()->month)
        ->whereYear('date', now()->year)
        ->whereNotNull('check_in')
        ->whereNotNull('check_out')
        ->get();
        
        $totalHours = 0;
        foreach ($attendances as $attendance) {
            if ($attendance->check_in && $attendance->check_out) {
                $totalHours += Carbon::parse($attendance->check_in)
                    ->diffInHours(Carbon::parse($attendance->check_out));
            }
        }
        
        return $totalHours;
    }

    // Chart data methods (placeholder implementations)
    protected function getTenantGrowthData()
    {
        if (!Schema::hasTable('tenants') || !class_exists('App\Models\Tenant')) {
            return ['labels' => [], 'datasets' => []];
        }
        
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = \App\Models\Tenant::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
            $months->push([
                'label' => $date->format('M'),
                'value' => $count
            ]);
        }
        
        return [
            'labels' => $months->pluck('label'),
            'datasets' => [
                [
                    'label' => 'New Tenants',
                    'data' => $months->pluck('value'),
                    'borderColor' => '#007bff',
                    'backgroundColor' => 'rgba(0, 123, 255, 0.1)',
                ]
            ]
        ];
    }

    protected function getRevenueByMonthData()
    {
        if (!Schema::hasTable('invoices')) {
            return ['labels' => [], 'datasets' => []];
        }
        
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $revenue = Invoice::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('total');
            $months->push([
                'label' => $date->format('M'),
                'value' => $revenue
            ]);
        }
        
        return [
            'labels' => $months->pluck('label'),
            'datasets' => [
                [
                    'label' => 'Revenue ($)',
                    'data' => $months->pluck('value'),
                    'borderColor' => '#28a745',
                    'backgroundColor' => 'rgba(40, 167, 69, 0.1)',
                ]
            ]
        ];
    }

    protected function getEmployeeDistributionData($tenantId)
    {
        $distribution = Employee::where('tenant_id', $tenantId)
            ->select('client_id', DB::raw('count(*) as count'))
            ->whereNotNull('client_id')
            ->groupBy('client_id')
            ->with('client:id,name')
            ->get();
            
        return [
            'labels' => $distribution->pluck('client.name'),
            'datasets' => [
                [
                    'data' => $distribution->pluck('count'),
                    'backgroundColor' => ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8'],
                ]
            ]
        ];
    }

    protected function getRevenueTrendData($tenantId)
    {
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $revenue = $this->getMonthlyRevenueForDate($tenantId, $date);
            $months->push([
                'label' => $date->format('M'),
                'value' => $revenue
            ]);
        }
        
        return [
            'labels' => $months->pluck('label'),
            'datasets' => [
                [
                    'label' => 'Monthly Revenue',
                    'data' => $months->pluck('value'),
                    'borderColor' => '#007bff',
                    'backgroundColor' => 'rgba(0, 123, 255, 0.1)',
                ]
            ]
        ];
    }

    protected function getMonthlyRevenueForDate($tenantId, $date)
    {
        if (!Schema::hasTable('invoices')) return 0;
        
        return Invoice::where('tenant_id', $tenantId)
            ->whereMonth('date', $date->month)
            ->whereYear('date', $date->year)
            ->sum('total');
    }

    protected function getAttendanceSummaryData($tenantId)
    {
        if (!Schema::hasTable('attendances')) return [];
        
        $summary = Attendance::where('tenant_id', $tenantId)
            ->whereMonth('date', now()->month)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');
            
        return [
            'labels' => ['Present', 'Absent', 'Leave'],
            'datasets' => [
                [
                    'data' => [
                        $summary['present'] ?? 0,
                        $summary['absent'] ?? 0,
                        $summary['leave'] ?? 0,
                    ],
                    'backgroundColor' => ['#28a745', '#dc3545', '#ffc107'],
                ]
            ]
        ];
    }

    // Activity feed methods (placeholder implementations)
    protected function getSystemActivity()
    {
        $activities = collect();
        
        // Recent tenants
        if (Schema::hasTable('tenants') && class_exists('App\Models\Tenant')) {
            $recentTenants = \App\Models\Tenant::latest()->take(3)->get();
            foreach ($recentTenants as $tenant) {
                $activities->push([
                    'title' => 'New tenant "' . ($tenant->name ?? $tenant->id) . '" registered',
                    'time' => $tenant->created_at->diffForHumans(),
                    'icon' => 'fas fa-building',
                    'color' => 'success',
                ]);
            }
        }
        
        // Recent invoices
        if (Schema::hasTable('invoices')) {
            $recentInvoices = Invoice::latest()->take(2)->get();
            foreach ($recentInvoices as $invoice) {
                $activities->push([
                    'title' => 'Invoice #' . $invoice->id . ' created ($' . number_format($invoice->total ?? 0, 2) . ')',
                    'time' => $invoice->created_at->diffForHumans(),
                    'icon' => 'fas fa-file-invoice-dollar',
                    'color' => 'primary',
                ]);
            }
        }
        
        return $activities->sortByDesc('time')->take(5)->values()->toArray();
    }

    protected function getAgencyActivity($tenantId)
    {
        $activities = collect();
        
        // Recent employees
        if (Schema::hasTable('employees')) {
            $recentEmployees = Employee::where('tenant_uuid', $tenantId)
                ->latest()->take(3)->get();
            foreach ($recentEmployees as $employee) {
                $activities->push([
                    'title' => 'New employee ' . ($employee->name ?? 'Employee #' . $employee->id) . ' registered',
                    'time' => $employee->created_at->diffForHumans(),
                    'icon' => 'fas fa-user-plus',
                    'color' => 'success',
                ]);
            }
        }
        
        // Recent payroll
        if (Schema::hasTable('payrolls')) {
            $recentPayroll = Payroll::where('tenant_uuid', $tenantId)
                ->latest()->first();
            if ($recentPayroll) {
                $activities->push([
                    'title' => 'Payroll processed for employee #' . $recentPayroll->employee_id,
                    'time' => $recentPayroll->created_at->diffForHumans(),
                    'icon' => 'fas fa-money-check',
                    'color' => 'primary',
                ]);
            }
        }
        
        // Recent invoices
        if (Schema::hasTable('invoices')) {
            $recentInvoices = Invoice::where('tenant_uuid', $tenantId)
                ->latest()->take(2)->get();
            foreach ($recentInvoices as $invoice) {
                $activities->push([
                    'title' => 'Invoice #' . $invoice->id . ' created ($' . number_format($invoice->total ?? 0, 2) . ')',
                    'time' => $invoice->created_at->diffForHumans(),
                    'icon' => 'fas fa-file-invoice',
                    'color' => 'info',
                ]);
            }
        }
        
        return $activities->sortByDesc('time')->take(5)->values()->toArray();
    }

    protected function getHRActivity($tenantId)
    {
        $activities = collect();
        
        // Recent attendance records
        if (Schema::hasTable('attendances')) {
            $recentAttendance = Attendance::where('tenant_uuid', $tenantId)
                ->latest()->take(3)->get();
            foreach ($recentAttendance as $attendance) {
                $activities->push([
                    'title' => 'Attendance marked for employee #' . $attendance->employee_id . ' (' . ($attendance->status ?? 'present') . ')',
                    'time' => $attendance->created_at->diffForHumans(),
                    'icon' => 'fas fa-clock',
                    'color' => $attendance->status === 'present' ? 'success' : 'warning',
                ]);
            }
        }
        
        // Recent employees
        if (Schema::hasTable('employees')) {
            $recentEmployees = Employee::where('tenant_uuid', $tenantId)
                ->latest()->take(2)->get();
            foreach ($recentEmployees as $employee) {
                $activities->push([
                    'title' => 'Employee ' . ($employee->name ?? '#' . $employee->id) . ' profile updated',
                    'time' => $employee->updated_at->diffForHumans(),
                    'icon' => 'fas fa-id-card',
                    'color' => 'primary',
                ]);
            }
        }
        
        return $activities->sortByDesc('time')->take(5)->values()->toArray();
    }

    protected function getClientActivity($clientId)
    {
        $activities = collect();
        
        // Recent attendance for client's employees
        if (Schema::hasTable('attendances') && Schema::hasTable('employees') && $clientId) {
            $recentAttendance = Attendance::whereHas('employee', function($query) use ($clientId) {
                $query->where('client_id', $clientId);
            })
            ->latest()->take(3)->get();
            
            foreach ($recentAttendance as $attendance) {
                $activities->push([
                    'title' => 'Guard shift ' . ($attendance->status === 'present' ? 'started' : 'ended') . ' - Employee #' . $attendance->employee_id,
                    'time' => $attendance->created_at->diffForHumans(),
                    'icon' => 'fas fa-shield-alt',
                    'color' => $attendance->status === 'present' ? 'success' : 'info',
                ]);
            }
        }
        
        // Recent invoices for this client
        if (Schema::hasTable('invoices') && $clientId) {
            $recentInvoices = Invoice::where('client_id', $clientId)
                ->latest()->take(2)->get();
            foreach ($recentInvoices as $invoice) {
                $activities->push([
                    'title' => 'Service invoice #' . $invoice->id . ' generated ($' . number_format($invoice->total ?? 0, 2) . ')',
                    'time' => $invoice->created_at->diffForHumans(),
                    'icon' => 'fas fa-file-alt',
                    'color' => 'info',
                ]);
            }
        }
        
        return $activities->sortByDesc('time')->take(5)->values()->toArray();
    }

    /**
     * Aggregate agency counts across all tenants for Super Admin dashboard.
     */
    protected function getNewAgencyCounts()
    {
        $counts = [
            'active' => 0,
            'inactive' => 0,
            'new_last_30' => 0,
        ];

        try {
            if (!class_exists('\App\\Models\\Tenant')) {
                return $counts;
            }

            $tenants = \App\Models\Tenant::all();
            foreach ($tenants as $tenant) {
                try {
                    tenancy()->run($tenant, function () use (&$counts) {
                        if (!Schema::hasTable('agencies')) return;

                        $counts['active'] += \App\Models\Agency::where('status', 'active')->count();
                        $counts['inactive'] += \App\Models\Agency::where('status', 'inactive')->count();
                        $counts['new_last_30'] += \App\Models\Agency::whereBetween('created_at', [now()->subDays(30), now()])->count();
                    });
                } catch (TenantCouldNotBeIdentifiedException $e) {
                    \Log::warning('Tenant switch failed for agency counts: ' . $e->getMessage());
                } catch (\Throwable $e) {
                    \Log::warning('Error aggregating agencies for a tenant: ' . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('Failed to compute agency counts: ' . $e->getMessage());
        }

        return $counts;
    }

    /**
     * List agencies across tenants filtered by status (for Super Admin).
     */
    public function agencyLeads(Request $request)
    {

        $status = $request->get('status', 'active');
        $agencies = [];

        try {
            $tenants = \App\Models\Tenant::all();
            foreach ($tenants as $tenant) {
                try {
                    tenancy()->run($tenant, function () use (&$agencies, $status, $tenant) {
                        if (!Schema::hasTable('agencies')) return;

                        $results = \App\Models\Agency::where('status', $status)
                            ->select('id', 'name', 'contact_name', 'contact_email', 'contact_phone', 'status', 'created_at')
                            ->limit(200)
                            ->get();

                        foreach ($results as $r) {
                            $agencies[] = [
                                'tenant_uuid' => $tenant->uuid ?? $tenant->id,
                                'tenant_name' => $tenant->name ?? $tenant->uuid ?? $tenant->id,
                                'agency_id' => $r->id,
                                'name' => $r->name,
                                'contact_name' => $r->contact_name ?? null,
                                'contact_email' => $r->contact_email ?? null,
                                'contact_phone' => $r->contact_phone ?? null,
                                'status' => $r->status,
                                'created_at' => $r->created_at,
                            ];
                        }
                    });
                } catch (\Throwable $e) {
                    \Log::warning('Error fetching agencies for tenant ' . ($tenant->uuid ?? $tenant->id) . ': ' . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('Failed to list agencies across tenants: ' . $e->getMessage());
        }

        // simple sorting by created_at desc
        usort($agencies, function ($a, $b) {
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
        });

        return view('admin.agency_leads.index', compact('agencies', 'status'));
    }

    /**
     * Agency followups listing (switches to tenant and returns followups if available).
     */
    public function agencyFollowups($tenantUuid, $agencyId)
    {
        try {
            $tenant = \App\Models\Tenant::where('uuid', $tenantUuid)->firstOrFail();
            $followups = [];

            tenancy()->run($tenant, function () use (&$followups, $agencyId) {
                if (!Schema::hasTable('agency_followups')) return;
                $model = class_exists('\App\\Models\\AgencyFollowup') ? \App\Models\AgencyFollowup::class : null;
                if (!$model) return;

                $followups = $model::where('agency_id', $agencyId)->with('leadPerson')->orderByDesc('followed_up_at')->get();
            });

            return view('admin.agency_leads.followups', compact('followups', 'tenantUuid', 'agencyId'));
        } catch (\Throwable $e) {
            \Log::warning('Failed to load followups: ' . $e->getMessage());
            return back()->with('error', 'Unable to load followups for that agency.');
        }
    }

    /**
     * Agency details page (aggregate client counts and recent followups)
     */
    public function agencyDetails($tenantUuid, $agencyId)
    {
        try {
            $tenant = \App\Models\Tenant::where('uuid', $tenantUuid)->firstOrFail();
            $details = ['total_active_clients' => 0, 'total_inactive_clients' => 0, 'recent_followups' => []];

            tenancy()->run($tenant, function () use (&$details, $agencyId) {
                if (Schema::hasTable('clients')) {
                    $details['total_active_clients'] = \App\Models\Client::where('agency_id', $agencyId)->where('status', 'active')->count();
                    $details['total_inactive_clients'] = \App\Models\Client::where('agency_id', $agencyId)->where('status', 'inactive')->count();
                }

                if (Schema::hasTable('agency_followups') && class_exists('\App\\Models\\AgencyFollowup')) {
                    $details['recent_followups'] = \App\Models\AgencyFollowup::where('agency_id', $agencyId)->latest('followed_up_at')->limit(10)->get();
                }
            });

            return view('admin.agency_leads.show', compact('details', 'tenantUuid', 'agencyId'));
        } catch (\Throwable $e) {
            \Log::warning('Failed to load agency details: ' . $e->getMessage());
            return back()->with('error', 'Unable to load agency details.');
        }
    }

    /**
     * Store a followup for an agency (Super Admin creating a record in tenant DB).
     */
    public function storeFollowup(Request $request, $tenantUuid, $agencyId)
    {
        if (!backpack_user() || !backpack_user()->hasRole('Super Admin')) {
            abort(403);
        }

        $data = $request->validate([
            'lead_person_id' => ['nullable','integer'],
            'communication_type' => ['required','string'],
            'notes' => ['nullable','string'],
            'followed_up_at' => ['nullable','date'],
        ]);

        try {
            $tenant = \App\Models\Tenant::where('uuid', $tenantUuid)->firstOrFail();

            tenancy()->run($tenant, function () use ($data, $agencyId) {
                if (!Schema::hasTable('agency_followups')) {
                    throw new \Exception('Followups table missing in tenant DB');
                }

                $create = [
                    'agency_id' => $agencyId,
                    'lead_person_id' => $data['lead_person_id'] ?? null,
                    'communication_type' => $data['communication_type'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'followed_up_at' => $data['followed_up_at'] ?? now(),
                    'created_by' => null,
                ];

                if (class_exists('\App\\Models\\AgencyFollowup')) {
                    \App\Models\AgencyFollowup::create($create);
                }
            });

            return redirect()->route('admin.agency.leads.followups', ['tenantUuid' => $tenantUuid, 'agencyId' => $agencyId])->with('success', 'Followup added');
        } catch (\Throwable $e) {
            \Log::warning('Failed to store followup: ' . $e->getMessage());
            return back()->with('error', 'Unable to create followup');
        }
    }

    // Additional chart data methods
    protected function getAttendanceTrendsData($tenantId)
    {
        if (!Schema::hasTable('attendances')) return [];
        
        $days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $present = Attendance::where('tenant_id', $tenantId)
                ->whereDate('date', $date)
                ->where('status', 'present')
                ->count();
            
            $days->push([
                'label' => $date->format('M j'),
                'value' => $present
            ]);
        }
        
        return [
            'labels' => $days->pluck('label'),
            'datasets' => [
                [
                    'label' => 'Present',
                    'data' => $days->pluck('value'),
                    'borderColor' => '#28a745',
                    'backgroundColor' => 'rgba(40, 167, 69, 0.1)',
                ]
            ]
        ];
    }

    protected function getEmployeeByClientData($tenantId)
    {
        $clients = Client::where('tenant_id', $tenantId)
            ->withCount('employees')
            ->get();
            
        return [
            'labels' => $clients->pluck('name'),
            'datasets' => [
                [
                    'data' => $clients->pluck('employees_count'),
                    'backgroundColor' => ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8'],
                ]
            ]
        ];
    }

    protected function getClientAttendanceData($clientId)
    {
        if (!Schema::hasTable('attendances') || !$clientId) return [];
        
        $summary = Attendance::whereHas('employee', function($query) use ($clientId) {
            $query->where('client_id', $clientId);
        })
        ->whereMonth('date', now()->month)
        ->select('status', DB::raw('count(*) as count'))
        ->groupBy('status')
        ->pluck('count', 'status');
        
        return [
            'labels' => ['Present', 'Absent', 'Leave'],
            'datasets' => [
                [
                    'data' => [
                        $summary['present'] ?? 0,
                        $summary['absent'] ?? 0,
                        $summary['leave'] ?? 0,
                    ],
                    'backgroundColor' => ['#28a745', '#dc3545', '#ffc107'],
                ]
            ]
        ];
    }

    // Dynamic trend calculation methods
    protected function calculateTenantTrend()
    {
        if (!Schema::hasTable('tenants') || !class_exists('App\\Models\\Tenant')) {
            return '0%';
        }
        
        $currentMonth = \App\Models\Tenant::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $lastMonth = \App\Models\Tenant::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        
        return $this->calculateTrend($currentMonth, $lastMonth);
    }

    protected function calculateRevenueTrend()
    {
        if (!Schema::hasTable('invoices')) {
            return '0%';
        }
        
        $currentMonth = Invoice::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');
        $lastMonth = Invoice::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('total');
        
        return $this->calculateTrend($currentMonth, $lastMonth);
    }

    protected function getClientServiceRating($clientId)
    {
        // Calculate service rating based on attendance rate and performance
        if (!$clientId || !Schema::hasTable('attendances')) {
            return '0.0/5';
        }
        
        $totalExpectedShifts = Employee::where('client_id', $clientId)->count() * now()->daysInMonth;
        $actualShifts = Attendance::whereHas('employee', function($query) use ($clientId) {
            $query->where('client_id', $clientId);
        })
        ->whereMonth('date', now()->month)
        ->where('status', 'present')
        ->count();
        
        if ($totalExpectedShifts == 0) return '0.0/5';
        
        $attendanceRate = ($actualShifts / $totalExpectedShifts);
        $rating = min(5.0, max(0.0, $attendanceRate * 5));
        
        return number_format($rating, 1) . '/5';
    }
}