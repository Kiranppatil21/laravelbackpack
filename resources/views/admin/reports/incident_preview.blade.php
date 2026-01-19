@extends(backpack_view('blank'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="la la-exclamation-triangle"></i> Incident Report Preview
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.reports.incident.index') }}" class="btn btn-sm btn-secondary">
                            <i class="la la-arrow-left"></i> Back
                        </a>
                        <a href="{{ route('admin.reports.incident.pdf', request()->all()) }}" class="btn btn-sm btn-danger" target="_blank">
                            <i class="la la-file-pdf"></i> Export PDF
                        </a>
                        <a href="{{ route('admin.reports.incident.excel', request()->all()) }}" class="btn btn-sm btn-success">
                            <i class="la la-file-excel"></i> Export Excel
                        </a>
                        <a href="{{ route('admin.reports.incident.csv', request()->all()) }}" class="btn btn-sm btn-info">
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
                                    <p class="mb-0">Total Incidents</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h3>{{ $stats['critical'] }}</h3>
                                    <p class="mb-0">Critical</p>
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
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h3>{{ $stats['resolved'] }}</h3>
                                    <p class="mb-0">Resolved</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Employee</th>
                                    <th>Client</th>
                                    <th>Type</th>
                                    <th>Severity</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($incidents as $incident)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($incident->incident_date)->format('d M Y H:i') }}</td>
                                    <td>{{ $incident->employee->name ?? 'N/A' }}</td>
                                    <td>{{ $incident->client->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-secondary">{{ ucfirst($incident->incident_type ?? 'general') }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $severityClass = match($incident->severity ?? 'low') {
                                                'critical' => 'danger',
                                                'high' => 'warning',
                                                'medium' => 'info',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge badge-{{ $severityClass }}">{{ ucfirst($incident->severity ?? 'low') }}</span>
                                    </td>
                                    <td>{{ \Str::limit($incident->description, 60) }}</td>
                                    <td>
                                        @php
                                            $statusClass = match($incident->status ?? 'pending') {
                                                'resolved' => 'success',
                                                'investigating' => 'info',
                                                'pending' => 'warning',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge badge-{{ $statusClass }}">{{ ucfirst($incident->status ?? 'pending') }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        <i class="la la-info-circle"></i> No incidents found matching your criteria
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
