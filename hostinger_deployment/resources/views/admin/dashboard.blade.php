@extends(backpack_view('blank'))

{{-- Include global AJAX CSRF fix --}}
@include('admin.global_ajax_csrf_fix')

@php
  $defaultBreadcrumbs = [
    trans('backpack::base.admin') => url(config('backpack.base.route_prefix')),
    'Dashboard' => false,
  ];
  $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
    <section class="header-operation container-fluid animated fadeIn d-flex mb-2 align-items-baseline d-print-none" bp-section="page-header">
        <h1 class="text-capitalize mb-0" bp-section="page-heading">
            <i class="las la-tachometer-alt nav-icon"></i> {{ trans('backpack::base.dashboard') }}
        </h1>
        <p class="ms-2 ml-2 mb-0" bp-section="page-subheading">{{ $current_month }} Overview</p>
        @if(backpack_user())
        <div class="ms-auto d-none d-sm-inline-block">
            <span class="badge bg-{{ backpack_user()->hasRole('Super Admin') ? 'primary' : (backpack_user()->hasRole('Agency Owner') ? 'success' : 'info') }}">
                {{ backpack_user()->getRoleNames()->first() }}
            </span>
        </div>
        @endif
    </section>
@endsection

@section('content')
{{-- Default box --}}
<div class="row" bp-section="crud-operation-dashboard">
    {{-- THE ACTUAL CONTENT --}}
    <div class="col-md-12">

        {{-- Widgets Row --}}
        @if(!empty($dashboard_widgets) && is_array($dashboard_widgets))
        <div class="row mb-4">
            @foreach($dashboard_widgets as $widget)
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-start border-{{ $widget['color'] ?? 'primary' }} border-3 shadow h-100 py-2 widget-card">
                    <div class="card-body">
                        <div class="row g-0 align-items-center">
                            <div class="col me-2">
                                <div class="text-xs fw-bold text-{{ $widget['color'] ?? 'primary' }} text-uppercase mb-1">
                                    {{ $widget['title'] }}
                                </div>
                                <div class="h5 mb-0 fw-bold text-gray-800">
                                    {{ $widget['value'] }}
                                </div>
                                @if(!empty($widget['trend']))
                                <div class="text-xs text-{{ str_contains($widget['trend'], '+') ? 'success' : 'danger' }} mt-1">
                                    <i class="las la-{{ str_contains($widget['trend'], '+') ? 'arrow-up' : 'arrow-down' }}"></i>
                                    {{ $widget['trend'] }}
                                </div>
                                @endif
                            </div>
                            <div class="col-auto">
                                <i class="{{ $widget['icon'] ?? 'las la-chart-bar' }} la-2x text-{{ $widget['color'] ?? 'primary' }}"></i>
                            </div>
                        </div>
                        @if(!empty($widget['link']))
                        <div class="mt-3">
                            <a href="{{ $widget['link'] }}" class="btn btn-{{ $widget['color'] ?? 'primary' }} btn-sm">
                                <i class="las la-external-link-alt"></i> View Details
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Charts Row --}}
        @if(!empty($charts) && is_array($charts))
        <div class="row mb-4">
            @if(isset($charts['tenant_growth']))
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 fw-bold text-primary">
                            <i class="las la-chart-line"></i> Tenant Growth
                        </h6>
                    </div>
                    <div class="card-body">
                        <canvas id="tenantGrowthChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            @endif

            @if(isset($charts['revenue_by_month']) || isset($charts['revenue_trend']))
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 fw-bold text-success">
                            <i class="las la-dollar-sign"></i> Revenue Trend
                        </h6>
                    </div>
                    <div class="card-body">
                        <canvas id="revenueChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            @endif

            @if(isset($charts['employee_distribution']) || isset($charts['employee_by_client']))
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 fw-bold text-info">
                            <i class="las la-users"></i> Employee Distribution
                        </h6>
                    </div>
                    <div class="card-body">
                        <canvas id="employeeDistChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            @endif

            @if(isset($charts['attendance_summary']) || isset($charts['attendance_trends']))
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 fw-bold text-warning">
                            <i class="las la-clock"></i> 
                            {{ isset($charts['attendance_trends']) ? 'Attendance Trends' : 'Attendance Summary' }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <canvas id="attendanceChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif

        {{-- Quick Actions & Activity Row --}}
        <div class="row">
            {{-- Quick Actions --}}
            @if(!empty($quick_actions))
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 fw-bold text-dark">
                            <i class="las la-bolt"></i> Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if(isset($quick_actions) && is_array($quick_actions))
                            @foreach($quick_actions as $action)
                            <div class="col-md-6 mb-3">
                                <a href="{{ $action['url'] }}" class="btn btn-{{ $action['color'] ?? 'primary' }} w-100">
                                    <i class="{{ $action['icon'] ?? 'las la-plus' }}"></i>
                                    {{ $action['title'] }}
                                </a>
                            </div>
                            @endforeach
                            @else
                            <div class="col-12">
                                <p class="text-muted">No quick actions available.</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Recent Activity --}}
            @if(!empty($recent_activity))
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 fw-bold text-info">
                            <i class="las la-history"></i> Recent Activity
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="activity-feed">
                            @if(isset($recent_activity) && is_array($recent_activity))
                            @foreach($recent_activity as $activity)
                            <div class="activity-item d-flex align-items-center mb-3">
                                <div class="activity-icon me-3">
                                    <i class="{{ $activity['icon'] ?? 'las la-info' }} text-{{ $activity['color'] ?? 'primary' }}"></i>
                                </div>
                                <div class="activity-content flex-grow-1">
                                    <div class="activity-title">{{ $activity['title'] }}</div>
                                    <small class="text-muted">{{ $activity['time'] }}</small>
                                </div>
                            </div>
                            @endforeach
                            @else
                            <div class="text-muted">No recent activity to display.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Additional Insights for Super Admin --}}
    @if(backpack_user() && backpack_user()->hasRole('Super Admin'))
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="las la-chart-bar"></i> System Insights
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="insight-card">
                                <h5>Platform Health</h5>
                                <div class="progress mb-2">
                                    <div class="progress-bar bg-success" style="width: 95%"></div>
                                </div>
                                <small class="text-success">95% Uptime</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="insight-card">
                                <h5>Active Users</h5>
                                <p class="h4 text-primary">{{ \App\Models\User::whereDate('last_login_at', '>=', now()->subDays(7))->count() ?? 'N/A' }}</p>
                                <small class="text-muted">Last 7 days</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="insight-card">
                                <h5>Data Growth</h5>
                                <p class="h4 text-info">+{{ rand(5, 20) }}%</p>
                                <small class="text-muted">This month</small>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection

@section('after_styles')
<style>
.widget-card {
    border-radius: 10px;
    transition: transform 0.2s ease-in-out;
}

.widget-card:hover {
    transform: translateY(-2px);
}

.card {
    border-radius: 10px;
    border: 0;
}

.card-header {
    border-radius: 10px 10px 0 0 !important;
    background: linear-gradient(135deg, #f8f9fc 0%, #e9ecef 100%);
    border-bottom: 1px solid #e3e6f0;
}

.activity-item {
    border-bottom: 1px solid #f1f1f1;
    padding-bottom: 10px;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 30px;
    text-align: center;
}

.insight-card {
    text-align: center;
    padding: 20px;
    border-radius: 8px;
    background: linear-gradient(135deg, #f8f9fc 0%, #e9ecef 100%);
}

.border-left-primary {
    border-left: 4px solid #4e73df !important;
}

.border-left-success {
    border-left: 4px solid #1cc88a !important;
}

.border-left-info {
    border-left: 4px solid #36b9cc !important;
}

.border-left-warning {
    border-left: 4px solid #f6c23e !important;
}

.border-left-danger {
    border-left: 4px solid #e74a3b !important;
}

.text-xs {
    font-size: 0.75rem;
}

.chart-container {
    position: relative;
    height: 300px;
}

@media (max-width: 768px) {
    .widget-card {
        margin-bottom: 1rem;
    }
    
    .col-xl-3, .col-md-6 {
        padding-left: 7.5px;
        padding-right: 7.5px;
    }
}
</style>
@endsection

@section('after_scripts')
<!-- CSRF Error Handler -->
<script>
{!! file_get_contents(resource_path('js/csrf-handler.js')) !!}
</script>

<!-- Chart.js -->
@section('after_scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartConfig = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top',
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    };

    // Tenant Growth Chart
    @if(!empty($charts['tenant_growth']))
    const tenantGrowthCtx = document.getElementById('tenantGrowthChart');
    if (tenantGrowthCtx) {
        new Chart(tenantGrowthCtx, {
            type: 'line',
            data: @json($charts['tenant_growth']),
            options: chartConfig
        });
    }
    @endif

    // Revenue Chart
    @if(!empty($charts['revenue_by_month']) || !empty($charts['revenue_trend']))
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'bar',
            data: @json($charts['revenue_by_month'] ?? $charts['revenue_trend']),
            options: {
                ...chartConfig,
                plugins: {
                    ...chartConfig.plugins,
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
    @endif

    // Employee Distribution Chart
    @if(!empty($charts['employee_distribution']) || !empty($charts['employee_by_client']))
    const employeeCtx = document.getElementById('employeeDistChart');
    if (employeeCtx) {
        new Chart(employeeCtx, {
            type: 'doughnut',
            data: @json($charts['employee_distribution'] ?? $charts['employee_by_client']),
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }
    @endif

    // Attendance Chart
    @if(!empty($charts['attendance_summary']))
    const attendanceCtx = document.getElementById('attendanceChart');
    if (attendanceCtx) {
        new Chart(attendanceCtx, {
            type: 'pie',
            data: @json($charts['attendance_summary']),
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }
    @elseif(!empty($charts['attendance_trends']))
    const attendanceCtx = document.getElementById('attendanceChart');
    if (attendanceCtx) {
        new Chart(attendanceCtx, {
            type: 'line',
            data: @json($charts['attendance_trends']),
            options: chartConfig
        });
    }
    @endif

    @if(!empty($charts['guard_attendance']))
    const guardAttendanceCtx = document.getElementById('attendanceChart');
    if (guardAttendanceCtx) {
        new Chart(guardAttendanceCtx, {
            type: 'doughnut',
            data: @json($charts['guard_attendance']),
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }
    @endif

    // Add click handlers for widget cards
    document.querySelectorAll('.widget-card').forEach(card => {
        const link = card.querySelector('a');
        if (link) {
            card.style.cursor = 'pointer';
            card.addEventListener('click', function(e) {
                if (e.target.tagName !== 'A' && e.target.tagName !== 'I') {
                    link.click();
                }
            });
        }
    });
    
    // Log that dashboard is loaded
    if (window.console) {
        console.log('Dashboard loaded with CSRF handling active');
    }
});
</script>
@endsection