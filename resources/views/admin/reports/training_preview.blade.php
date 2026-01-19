@extends(backpack_view('blank'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="la la-graduation-cap"></i> Training Report Preview
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.reports.training.index') }}" class="btn btn-sm btn-secondary">
                            <i class="la la-arrow-left"></i> Back
                        </a>
                        <a href="{{ route('admin.reports.training.pdf', request()->all()) }}" class="btn btn-sm btn-danger" target="_blank">
                            <i class="la la-file-pdf"></i> Export PDF
                        </a>
                        <a href="{{ route('admin.reports.training.excel', request()->all()) }}" class="btn btn-sm btn-success">
                            <i class="la la-file-excel"></i> Export Excel
                        </a>
                        <a href="{{ route('admin.reports.training.csv', request()->all()) }}" class="btn btn-sm btn-info">
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
                                    <p class="mb-0">Total Trainings</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h3>{{ $stats['completed'] }}</h3>
                                    <p class="mb-0">Completed</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h3>{{ $stats['ongoing'] }}</h3>
                                    <p class="mb-0">Ongoing</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h3>{{ $stats['participants'] }}</h3>
                                    <p class="mb-0">Total Participants</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Training Name</th>
                                    <th>Trainer</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Duration (hrs)</th>
                                    <th>Participants</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($trainings as $training)
                                <tr>
                                    <td>{{ $training->title }}</td>
                                    <td>{{ $training->trainer_name }}</td>
                                    <td>{{ \Carbon\Carbon::parse($training->start_date)->format('d M Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($training->end_date)->format('d M Y') }}</td>
                                    <td>{{ $training->duration ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ $training->participants_count ?? 0 }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $badgeClass = match($training->status ?? 'scheduled') {
                                                'completed' => 'success',
                                                'ongoing' => 'warning',
                                                'cancelled' => 'danger',
                                                default => 'info'
                                            };
                                        @endphp
                                        <span class="badge badge-{{ $badgeClass }}">{{ ucfirst($training->status ?? 'scheduled') }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        <i class="la la-info-circle"></i> No trainings found matching your criteria
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
