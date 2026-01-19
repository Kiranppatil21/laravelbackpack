@extends(backpack_view('blank'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="la la-file-alt"></i> Leave Report Generator
                    </h3>
                </div>
                
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.reports.leave.generate') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="leave_type" class="form-label">Leave Type</label>
                            <select class="form-control" id="leave_type" name="leave_type">
                                <option value="">All Types</option>
                                @foreach($leaveTypes as $type)
                                    <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">All Statuses</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="la la-search"></i> Generate Report
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                <i class="la la-redo"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                    <h4 class="card-title"><i class="la la-info-circle"></i> Instructions</h4>
                </div>
                <div class="card-body">
                    <ul>
                        <li>Select date range to filter leaves within specific period</li>
                        <li>Choose leave type to filter by casual, sick, annual, etc.</li>
                        <li>Filter by status: pending, approved, rejected, or cancelled</li>
                        <li>Click "Generate Report" to preview the report</li>
                        <li>From preview, you can export to PDF, Excel, or CSV</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
