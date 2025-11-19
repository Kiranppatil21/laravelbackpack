@extends(backpack_view('blank'))

@php
  $defaultBreadcrumbs = [
    'Admin' => url(config('backpack.base.route_prefix'), 'dashboard'),
    'Bulk Attendance' => route('admin.bulk-attendance.index'),
    'View Records' => false,
  ];
  $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
<section class="container-fluid">
  <h2>
    <span class="text-capitalize">Attendance Records</span>
    <small>View and manage bulk attendance records</small>
  </h2>
</section>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Attendance Records</h3>
                <a href="{{ route('admin.bulk-attendance.index') }}" class="btn btn-primary">
                    <i class="la la-plus"></i> Create New Attendance
                </a>
            </div>
            <div class="card-body">
                @if($attendanceRecords->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Site Name</th>
                                    <th>Month</th>
                                    <th>User Type</th>
                                    <th>Total Employees</th>
                                    <th>Total Days</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attendanceRecords as $record)
                                    <tr>
                                        <td>{{ $loop->iteration + ($attendanceRecords->currentPage() - 1) * $attendanceRecords->perPage() }}</td>
                                        <td>
                                            <strong>{{ $record->site->name ?? 'N/A' }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $record->formatted_month }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $record->user_type }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $record->total_employees }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">{{ $record->total_working_days }}</span>
                                        </td>
                                        <td>{{ $record->creator->name ?? 'N/A' }}</td>
                                        <td>{{ $record->created_at->format('d-m-Y H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.bulk-attendance.show', $record->id) }}" 
                                                   class="btn btn-sm btn-info" 
                                                   title="View Details">
                                                    <i class="la la-eye"></i>
                                                </a>
                                                
                                                <form action="{{ route('admin.bulk-attendance.destroy', $record->id) }}" 
                                                      method="POST" 
                                                      style="display: inline;"
                                                      onsubmit="return confirm('Are you sure you want to delete this attendance record? This action cannot be undone.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-danger" 
                                                            title="Delete Record">
                                                        <i class="la la-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="d-flex justify-content-center">
                        {{ $attendanceRecords->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="la la-calendar-times-o" style="font-size: 4rem; color: #ccc;"></i>
                        <h4 class="mt-3 text-muted">No Attendance Records Found</h4>
                        <p class="text-muted">Create your first bulk attendance record to get started.</p>
                        <a href="{{ route('admin.bulk-attendance.index') }}" class="btn btn-primary">
                            <i class="la la-plus"></i> Create New Attendance
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('after_styles')
<style>
    .btn-group .btn {
        margin: 0 2px;
    }
    
    .badge {
        font-size: 0.75rem;
    }
    
    .table th {
        border-top: none;
        font-weight: 600;
    }
    
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
</style>
@endpush