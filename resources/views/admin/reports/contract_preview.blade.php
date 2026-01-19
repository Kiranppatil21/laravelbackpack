@extends(backpack_view('blank'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="la la-file-contract"></i> Contract Report Preview
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.reports.contract.index') }}" class="btn btn-sm btn-secondary">
                            <i class="la la-arrow-left"></i> Back
                        </a>
                        <a href="{{ route('admin.reports.contract.pdf', request()->all()) }}" class="btn btn-sm btn-danger" target="_blank">
                            <i class="la la-file-pdf"></i> Export PDF
                        </a>
                        <a href="{{ route('admin.reports.contract.excel', request()->all()) }}" class="btn btn-sm btn-success">
                            <i class="la la-file-excel"></i> Export Excel
                        </a>
                        <a href="{{ route('admin.reports.contract.csv', request()->all()) }}" class="btn btn-sm btn-info">
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
                                    <p class="mb-0">Total Contracts</p>
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
                                    <h3>{{ $stats['expiring'] }}</h3>
                                    <p class="mb-0">Expiring Soon</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h3>{{ $stats['expired'] }}</h3>
                                    <p class="mb-0">Expired</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Contract #</th>
                                    <th>Client</th>
                                    <th>Service Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Value</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($contracts as $contract)
                                <tr>
                                    <td>{{ $contract->contract_number ?? 'N/A' }}</td>
                                    <td>{{ $contract->client->name ?? 'N/A' }}</td>
                                    <td>{{ $contract->service_type ?? 'N/A' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($contract->start_date)->format('d M Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($contract->end_date)->format('d M Y') }}</td>
                                    <td>{{ $contract->contract_value ? '₹' . number_format($contract->contract_value, 2) : 'N/A' }}</td>
                                    <td>
                                        @php
                                            $endDate = \Carbon\Carbon::parse($contract->end_date);
                                            $now = now();
                                            $daysLeft = $now->diffInDays($endDate, false);
                                            
                                            if ($daysLeft < 0) {
                                                $badgeClass = 'danger';
                                                $statusText = 'Expired';
                                            } elseif ($daysLeft <= 30) {
                                                $badgeClass = 'warning';
                                                $statusText = 'Expiring Soon';
                                            } else {
                                                $badgeClass = 'success';
                                                $statusText = 'Active';
                                            }
                                        @endphp
                                        <span class="badge badge-{{ $badgeClass }}">{{ $statusText }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        <i class="la la-info-circle"></i> No contracts found matching your criteria
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
