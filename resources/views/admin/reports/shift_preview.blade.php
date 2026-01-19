@extends(backpack_view('blank'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="la la-clock"></i> Shift Report Preview
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.reports.shift.index') }}" class="btn btn-sm btn-secondary">
                            <i class="la la-arrow-left"></i> Back
                        </a>
                        <a href="{{ route('admin.reports.shift.pdf', request()->all()) }}" class="btn btn-sm btn-danger" target="_blank">
                            <i class="la la-file-pdf"></i> Export PDF
                        </a>
                        <a href="{{ route('admin.reports.shift.excel', request()->all()) }}" class="btn btn-sm btn-success">
                            <i class="la la-file-excel"></i> Export Excel
                        </a>
                        <a href="{{ route('admin.reports.shift.csv', request()->all()) }}" class="btn btn-sm btn-info">
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
                                    <p class="mb-0">Total Shifts</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h3>{{ $stats['active'] }}</h3>
                                    <p class="mb-0">Active</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h3>{{ $stats['completed'] }}</h3>
                                    <p class="mb-0">Completed</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h3>{{ $stats['total_hours'] }}</h3>
                                    <p class="mb-0">Total Hours</p>
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
                                    <th>Shift Name</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Date</th>
                                    <th>Client</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($shifts as $shift)
                                <tr>
                                    <td>{{ $shift->employee->name ?? 'N/A' }}</td>
                                    <td>{{ $shift->shift_name }}</td>
                                    <td>{{ $shift->start_time }}</td>
                                    <td>{{ $shift->end_time }}</td>
                                    <td>{{ \Carbon\Carbon::parse($shift->date)->format('d M Y') }}</td>
                                    <td>{{ $shift->client->name ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            $badgeClass = match($shift->status ?? 'scheduled') {
                                                'completed' => 'success',
                                                'active' => 'info',
                                                'cancelled' => 'danger',
                                                default => 'warning'
                                            };
                                        @endphp
                                        <span class="badge badge-{{ $badgeClass }}">{{ ucfirst($shift->status ?? 'scheduled') }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        <i class="la la-info-circle"></i> No shifts found matching your criteria
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
