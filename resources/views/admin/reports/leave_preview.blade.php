@extends(backpack_view('blank'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="la la-file-alt"></i> Leave Report Preview
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.reports.leave.index') }}" class="btn btn-sm btn-secondary">
                            <i class="la la-arrow-left"></i> Back
                        </a>
                        <a href="{{ route('admin.reports.leave.pdf', request()->all()) }}" class="btn btn-sm btn-danger" target="_blank">
                            <i class="la la-file-pdf"></i> Export PDF
                        </a>
                        <a href="{{ route('admin.reports.leave.excel', request()->all()) }}" class="btn btn-sm btn-success">
                            <i class="la la-file-excel"></i> Export Excel
                        </a>
                        <a href="{{ route('admin.reports.leave.csv', request()->all()) }}" class="btn btn-sm btn-info">
                            <i class="la la-file-csv"></i> Export CSV
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h3>{{ $stats['total'] }}</h3>
                                    <p class="mb-0">Total Leaves</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h3>{{ $stats['approved'] }}</h3>
                                    <p class="mb-0">Approved</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h3>{{ $stats['pending'] }}</h3>
                                    <p class="mb-0">Pending</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h3>{{ $stats['total_days'] }}</h3>
                                    <p class="mb-0">Total Days</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Days</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leaves as $leave)
                                <tr>
                                    <td>{{ $leave->employee->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ ucfirst($leave->leave_type) }}</span>
                                    </td>
                                    <td>{{ $leave->start_date->format('d M Y') }}</td>
                                    <td>{{ $leave->end_date->format('d M Y') }}</td>
                                    <td>{{ $leave->days }}</td>
                                    <td>{{ \Str::limit($leave->reason, 50) }}</td>
                                    <td>
                                        @php
                                            $badgeClass = match($leave->status) {
                                                'approved' => 'success',
                                                'pending' => 'warning',
                                                'rejected' => 'danger',
                                                'cancelled' => 'secondary',
                                                default => 'info'
                                            };
                                        @endphp
                                        <span class="badge badge-{{ $badgeClass }}">{{ ucfirst($leave->status) }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        <i class="la la-info-circle"></i> No leaves found matching your criteria
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
