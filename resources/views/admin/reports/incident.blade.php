@extends(backpack_view('blank'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="la la-file-alt"></i> Incident Report Generator
                    </h3>
                </div>
                
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.reports.incident.generate') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="incident_type" class="form-label">Incident Type</label>
                            <select class="form-control" id="incident_type" name="incident_type">
                                <option value="">All Types</option>
                                @foreach($incidentTypes as $type)
                                    <option value="{{ $type }}">{{ ucfirst(str_replace('-', ' ', $type)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="severity" class="form-label">Severity</label>
                            <select class="form-control" id="severity" name="severity">
                                <option value="">All</option>
                                @foreach($severities as $severity)
                                    <option value="{{ $severity }}">{{ ucfirst($severity) }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="la la-search"></i> Generate Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
