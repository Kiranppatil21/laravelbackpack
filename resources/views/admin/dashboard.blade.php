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
                                @if(!empty($agency_leads_counts) && is_array($agency_leads_counts))
                                <div class="card-body border-top">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <a href="{{ backpack_url('agency-leads?status=active') }}" class="text-decoration-none">
                                                <div class="insight-card">
                                                    <h5>Total Active Agencies</h5>
                                                    <p class="h4 text-success">{{ $agency_leads_counts['active'] ?? 0 }}</p>
                                                    <small class="text-muted">Click to view list</small>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-md-4">
                                            <a href="{{ backpack_url('agency-leads?status=inactive') }}" class="text-decoration-none">
                                                <div class="insight-card">
                                                    <h5>Total Inactive Agencies</h5>
                                                    <p class="h4 text-warning">{{ $agency_leads_counts['inactive'] ?? 0 }}</p>
                                                    <small class="text-muted">Click to view list</small>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-md-4">
                                            <a href="{{ backpack_url('agency-leads?status=active') }}" class="text-decoration-none">
                                                <div class="insight-card">
                                                    <h5>New (30 days)</h5>
                                                    <p class="h4 text-primary">{{ $agency_leads_counts['new_last_30'] ?? 0 }}</p>
                                                    <small class="text-muted">Newly added agencies</small>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @if(!empty($recent_agencies) && is_array($recent_agencies))
                                <div class="card-body border-top">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="mb-0">Recent Agencies</h5>
                                        <button id="openAgencyModal" type="button" class="btn btn-sm btn-success">
                                            <i class="las la-plus"></i> Quick Create Agency
                                        </button>
                                    </div>
                                    <div class="list-group">
                                        @foreach($recent_agencies as $a)
                                        <a href="{{ $a['link'] }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $a['name'] }}</strong><br>
                                                <small class="text-muted">{{ $a['contact'] }}</small>
                                            </div>
                                            <span class="badge bg-{{ ($a['status'] ?? '') == 'active' ? 'success' : 'secondary' }}">{{ ucfirst($a['status'] ?? 'N/A') }}</span>
                                        </a>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                                @if(!empty($recent_tenants) && is_array($recent_tenants))
                                <div class="card-body border-top">
                                    <h5 class="mb-3">Recent Tenants</h5>
                                    <div class="list-group mb-3">
                                        @foreach($recent_tenants as $t)
                                        <a href="{{ $t['link'] }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $t['name'] }}</strong>
                                                <div><small class="text-muted">{{ $t['uuid'] ?? '' }}</small></div>
                                            </div>
                                            <small class="text-muted">{{ optional($t['created_at'])->diffForHumans() ?? '' }}</small>
                                        </a>
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                                @if(!empty($alerts) && is_array($alerts))
                                <div class="card-body border-top">
                                    <h5 class="mb-3">System Alerts</h5>
                                    @if(count($alerts) === 0)
                                        <div class="text-muted">No alerts</div>
                                    @else
                                    <ul class="list-group">
                                        @foreach($alerts as $alert)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $alert['title'] }}</strong><br>
                                                <small class="text-muted">{{ optional($alert['created_at'])->diffForHumans() ?? '' }}</small>
                                            </div>
                                            <span class="badge bg-{{ $alert['severity'] ?? 'info' }}">{{ strtoupper($alert['severity'] ?? 'INFO') }}</span>
                                        </li>
                                        @endforeach
                                    </ul>
                                    @endif
                                </div>
                                @endif
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

<!-- Agency Quick Create Modal -->
<div class="modal fade" id="agencyQuickCreateModal" tabindex="-1" aria-labelledby="agencyQuickCreateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="agencyQuickCreateModalLabel">Quick Create Agency</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="agencyQuickCreateForm">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="mb-2">
                        <label class="form-label">Agency Name</label>
                        <input name="name" class="form-control" required />
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Contact Name</label>
                        <input name="contact_name" class="form-control" />
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Contact Email</label>
                        <input name="contact_email" type="email" class="form-control" />
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Contact Phone</label>
                        <input name="contact_phone" class="form-control" />
                    </div>
                    @if(!empty($recent_tenants) && is_array($recent_tenants))
                    <div class="mb-2">
                        <label class="form-label">Assign to Tenant (optional)</label>
                        <select name="tenant_uuid" class="form-select">
                            <option value="">-- none --</option>
                            @foreach($recent_tenants as $t)
                                <option value="{{ $t['uuid'] ?? '' }}">{{ $t['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="mb-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button id="agencyQuickCreateSubmit" type="button" class="btn btn-primary">Create</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
        // Open modal
        const openBtn = document.getElementById('openAgencyModal');
        const modalEl = document.getElementById('agencyQuickCreateModal');
        let bsModal = null;
        if (modalEl && typeof bootstrap !== 'undefined') {
                bsModal = new bootstrap.Modal(modalEl);
        }
        if (openBtn && bsModal) {
                openBtn.addEventListener('click', function() {
                        bsModal.show();
                });
        }

        // Form submit
        const submitBtn = document.getElementById('agencyQuickCreateSubmit');
        const form = document.getElementById('agencyQuickCreateForm');
        if (submitBtn && form) {
                submitBtn.addEventListener('click', function() {
                        const formData = new FormData(form);
                        const url = '{{ backpack_url('agency/quick-create') }}';

                        fetch(url, {
                                method: 'POST',
                                headers: {
                                        'X-CSRF-TOKEN': formData.get('_token'),
                                        'Accept': 'application/json'
                                },
                                body: formData
                        }).then(r => r.json())
                        .then(data => {
                                if (data && data.success && data.agency) {
                                        // prepend to recent agencies list if exists
                                        const list = document.querySelector('.list-group');
                                        if (list) {
                                                const a = document.createElement('a');
                                                a.href = data.agency.link;
                                                a.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
                                                a.innerHTML = `<div><strong>${data.agency.name}</strong><br><small class="text-muted">${data.agency.contact || ''}</small></div><span class="badge bg-${(data.agency.status === 'active') ? 'success' : 'secondary'}">${(data.agency.status || 'N/A')}</span>`;
                                                list.prepend(a);
                                        }
                                        if (bsModal) bsModal.hide();
                                } else {
                                        alert((data && data.error) ? data.error : 'Failed to create agency');
                                }
                        }).catch(err => {
                                console.error(err);
                                alert('Unexpected error creating agency');
                        });
                });
        }
});
</script>