@extends(backpack_view('blank'))

@php
  $defaultBreadcrumbs = [
    'Admin' => url(config('backpack.base.route_prefix'), 'dashboard'),
    'Bulk Attendance' => route('admin.bulk-attendance.index'),
    'View Records' => route('admin.bulk-attendance.view'),
    'Details' => false,
  ];
  $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
<section class="container-fluid">
  <h2>
    <span class="text-capitalize">Attendance Details</span>
    <small>{{ $master->site->name ?? 'N/A' }} - {{ $master->formatted_month }}</small>
  </h2>
</section>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        
        {{-- Summary Card --}}
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Attendance Summary</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.bulk-attendance.view') }}" class="btn btn-sm btn-secondary">
                        <i class="la la-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <div class="info-box bg-info">
                            <span class="info-box-icon"><i class="la la-building"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Site</span>
                                <span class="info-box-number">{{ $master->site->name ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="info-box bg-primary">
                            <span class="info-box-icon"><i class="la la-calendar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Month</span>
                                <span class="info-box-number">{{ $master->formatted_month }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="info-box bg-secondary">
                            <span class="info-box-icon"><i class="la la-user"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">User Type</span>
                                <span class="info-box-number">{{ $master->user_type }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="info-box bg-warning">
                            <span class="info-box-icon"><i class="la la-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Employees</span>
                                <span class="info-box-number">{{ $attendanceByEmployee->count() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="info-box bg-success">
                            <span class="info-box-icon"><i class="la la-clock-o"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Days</span>
                                <span class="info-box-number">{{ $master->total_working_days }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="info-box bg-danger">
                            <span class="info-box-icon"><i class="la la-plus-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">OT Days</span>
                                <span class="info-box-number">{{ $master->details()->where('is_ot', true)->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Attendance Details Table --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Attendance Grid</h3>
                <div class="card-tools">
                    <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="collapse" 
                            data-bs-target="#attendanceTable" aria-expanded="true">
                        <i class="la la-eye"></i> Toggle View
                    </button>
                </div>
            </div>
            <div class="card-body collapse show" id="attendanceTable">
                @if($attendanceByEmployee->count() > 0)
                    <div class="table-responsive" style="max-height: 600px; overflow-x: auto;">
                        <table class="table table-bordered table-hover table-sm">
                            <thead class="table-dark sticky-top">
                                <tr>
                                    <th>Sr. No</th>
                                    <th>Employee Name</th>
                                    <th>Position</th>
                                    @foreach($calendar as $day)
                                        <th class="day-header {{ $day['is_weekend'] ? 'weekend-header' : '' }}">
                                            <div>{{ $day['day'] }}</div>
                                            <div class="small">{{ $day['day_name'] }}</div>
                                        </th>
                                    @endforeach
                                    <th>Total Days</th>
                                    <th>OT Days</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attendanceByEmployee as $employeeId => $data)
                                    @php
                                        $employee = $data['employee'];
                                        $days = $data['days'];
                                        $totalDays = $days->count();
                                        $otDays = $days->where('is_ot', true)->count();
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <strong>{{ $employee->first_name }} {{ $employee->last_name }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $employee->position }}</span>
                                        </td>
                                        @foreach($calendar as $day)
                                            @php
                                                $attendance = $days->get($day['date']);
                                                $isWeekend = $day['is_weekend'];
                                            @endphp
                                            <td class="day-cell text-center {{ $isWeekend ? 'weekend-cell' : '' }}">
                                                @if($attendance)
                                                    <div>
                                                        <span class="badge bg-{{ $attendance['shift'] == '1' ? 'primary' : ($attendance['shift'] == '2' ? 'info' : 'success') }}">
                                                            S{{ $attendance['shift'] }}
                                                        </span>
                                                    </div>
                                                    @if($attendance['is_ot'])
                                                        <div>
                                                            <span class="badge bg-warning small">OT</span>
                                                        </div>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="text-center">
                                            <span class="badge bg-success">{{ $totalDays }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-warning">{{ $otDays }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="la la-exclamation-triangle" style="font-size: 4rem; color: #ccc;"></i>
                        <h4 class="mt-3 text-muted">No Attendance Data</h4>
                        <p class="text-muted">No attendance details found for this record.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Employee-wise Summary --}}
        @if($attendanceByEmployee->count() > 0)
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Employee Summary</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($attendanceByEmployee as $employeeId => $data)
                            @php
                                $employee = $data['employee'];
                                $days = $data['days'];
                                $totalDays = $days->count();
                                $otDays = $days->where('is_ot', true)->count();
                                $shift1Days = $days->where('shift', '1')->count();
                                $shift2Days = $days->where('shift', '2')->count();
                                $shift3Days = $days->where('shift', '3')->count();
                            @endphp
                            <div class="col-md-4 mb-3">
                                <div class="card border">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $employee->first_name }} {{ $employee->last_name }}</h5>
                                        <h6 class="card-subtitle mb-2 text-muted">{{ $employee->position }}</h6>
                                        
                                        <div class="row text-center">
                                            <div class="col-3">
                                                <div class="small text-muted">Total</div>
                                                <div class="h5 text-success">{{ $totalDays }}</div>
                                            </div>
                                            <div class="col-3">
                                                <div class="small text-muted">OT</div>
                                                <div class="h5 text-warning">{{ $otDays }}</div>
                                            </div>
                                            <div class="col-2">
                                                <div class="small text-muted">S1</div>
                                                <div class="small text-primary">{{ $shift1Days }}</div>
                                            </div>
                                            <div class="col-2">
                                                <div class="small text-muted">S2</div>
                                                <div class="small text-info">{{ $shift2Days }}</div>
                                            </div>
                                            <div class="col-2">
                                                <div class="small text-muted">S3</div>
                                                <div class="small text-success">{{ $shift3Days }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('after_styles')
<style>
    .info-box {
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    .day-header {
        writing-mode: vertical-lr;
        text-orientation: mixed;
        min-width: 60px;
        max-width: 60px;
        text-align: center;
        font-size: 0.75rem;
        padding: 8px 4px;
    }
    
    .weekend-header {
        background-color: #f8d7da !important;
        color: #721c24 !important;
    }
    
    .day-cell {
        min-width: 60px;
        max-width: 60px;
        vertical-align: middle;
        padding: 4px;
    }
    
    .weekend-cell {
        background-color: #f8f9fa;
    }
    
    .badge {
        font-size: 0.7rem;
    }
    
    .card {
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    
    .table-responsive {
        border: 1px solid #dee2e6;
        border-radius: 4px;
    }
    
    .info-box-number {
        font-size: 0.9rem !important;
        font-weight: 600;
    }
    
    .info-box-text {
        font-size: 0.8rem;
    }
</style>
@endpush