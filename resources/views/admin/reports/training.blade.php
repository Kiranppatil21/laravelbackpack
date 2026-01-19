@extends(backpack_view('blank'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="la la-file-alt"></i> Training Report Generator
                    </h3>
                </div>
                
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.reports.training.generate') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-control" id="category" name="category">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}">{{ ucfirst(str_replace('-', ' ', $category)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">All Statuses</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="start_date" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date">
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
