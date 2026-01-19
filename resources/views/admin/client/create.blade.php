@extends(backpack_view('blank'))

@php
    $defaultBreadcrumbs = [
      trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
      'Clients' => backpack_url('client'),
      'Create' => false,
    ];

    // if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
    $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
    <section class="container-fluid">
      <h2>
        <span class="text-capitalize">Create Client</span>
        <small>Add a new client to the system.</small>
      </h2>
    </section>
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Success!</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(isset($errors) && $errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Error!</strong> Please fix the following issues:
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row">
    <div class="col-md-12">
        <form method="post" action="{{ route('client.store-custom') }}" enctype="multipart/form-data">
            @csrf

            <div class="card">
                <div class="card-body row">
                    
                    <!-- Basic Information -->
                    <h5 class="col-12 mb-3">Basic Information</h5>
                    
                    <div class="form-group col-sm-6">
                        <label>Serial Number</label>
                        <input type="number" name="serial_no" class="form-control @error('serial_no') is-invalid @enderror" 
                               value="{{ old('serial_no', $nextSerialNo) }}">
                        @error('serial_no')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-sm-6">
                        <label>Company *</label>
                        <select name="company_id" class="form-control @error('company_id') is-invalid @enderror" required>
                            <option value="">Select Company</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('company_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-sm-6">
                        <label>Client Name *</label>
                        <input type="text" name="name_of_client" class="form-control @error('name_of_client') is-invalid @enderror" 
                               value="{{ old('name_of_client') }}" required>
                        @error('name_of_client')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-sm-6">
                        <label>Email *</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                               value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-sm-6">
                        <label>To Title</label>
                        <input type="text" name="to_title" class="form-control @error('to_title') is-invalid @enderror" 
                               value="{{ old('to_title') }}">
                        @error('to_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-sm-6">
                        <label>Site Name</label>
                        <input type="text" name="site_name" class="form-control @error('site_name') is-invalid @enderror" 
                               value="{{ old('site_name') }}">
                        @error('site_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Contact Information -->
                    <h5 class="col-12 mt-4 mb-3">Contact Information</h5>

                    <div class="form-group col-sm-12">
                        <label>Address</label>
                        <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="3">{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-sm-4">
                        <label>Primary Contact</label>
                        <input type="text" name="contact_no_1" class="form-control @error('contact_no_1') is-invalid @enderror" 
                               value="{{ old('contact_no_1') }}">
                        @error('contact_no_1')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-sm-4">
                        <label>Secondary Contact</label>
                        <input type="text" name="contact_no_2" class="form-control @error('contact_no_2') is-invalid @enderror" 
                               value="{{ old('contact_no_2') }}">
                        @error('contact_no_2')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-sm-4">
                        <label>Site Supervisor Contact</label>
                        <input type="text" name="site_supervisor_contact" class="form-control @error('site_supervisor_contact') is-invalid @enderror" 
                               value="{{ old('site_supervisor_contact') }}">
                        @error('site_supervisor_contact')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-sm-6">
                        <label>Site Admin Contact</label>
                        <input type="text" name="site_admin_contact" class="form-control @error('site_admin_contact') is-invalid @enderror" 
                               value="{{ old('site_admin_contact') }}">
                        @error('site_admin_contact')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-sm-6">
                        <label>Site Manager Contact</label>
                        <input type="text" name="site_manager_contact" class="form-control @error('site_manager_contact') is-invalid @enderror" 
                               value="{{ old('site_manager_contact') }}">
                        @error('site_manager_contact')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Tax Information -->
                    <h5 class="col-12 mt-4 mb-3">Tax Information</h5>

                    <div class="form-group col-sm-4">
                        <label>GST Number</label>
                        <input type="text" name="gst_no" class="form-control @error('gst_no') is-invalid @enderror" 
                               value="{{ old('gst_no') }}">
                        @error('gst_no')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-sm-4">
                        <label>PAN Number</label>
                        <input type="text" name="pan_no" class="form-control @error('pan_no') is-invalid @enderror" 
                               value="{{ old('pan_no') }}">
                        @error('pan_no')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-sm-4">
                        <label>TDS Percentage</label>
                        <input type="number" step="0.01" name="tds_percentage" class="form-control @error('tds_percentage') is-invalid @enderror" 
                               value="{{ old('tds_percentage') }}">
                        @error('tds_percentage')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Personal Information -->
                    <h5 class="col-12 mt-4 mb-3">Personal Information</h5>

                    <div class="form-group col-sm-6">
                        <label>Date of Birth</label>
                        <input type="date" name="dob" class="form-control @error('dob') is-invalid @enderror" 
                               value="{{ old('dob') }}">
                        @error('dob')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-sm-6">
                        <label>Anniversary Date</label>
                        <input type="date" name="date_of_anniversary" class="form-control @error('date_of_anniversary') is-invalid @enderror" 
                               value="{{ old('date_of_anniversary') }}">
                        @error('date_of_anniversary')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Authentication -->
                    <h5 class="col-12 mt-4 mb-3">Login Credentials</h5>

                    <div class="form-group col-sm-6">
                        <label>Login Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name') }}">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-sm-6">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Financial / Billing Fields -->
                    <h5 class="col-12 mt-4 mb-3">Financial / Billing</h5>

                    <div class="form-group col-sm-4">
                        <label>Billing Rate</label>
                        <input type="number" step="0.01" min="0" name="billing_rate" class="form-control @error('billing_rate') is-invalid @enderror" 
                               value="{{ old('billing_rate') }}">
                        @error('billing_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-sm-4">
                        <label>Salary Cost</label>
                        <input type="number" step="0.01" min="0" name="salary_cost" class="form-control @error('salary_cost') is-invalid @enderror" 
                               value="{{ old('salary_cost') }}">
                        @error('salary_cost')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-sm-4">
                        <label>ESI Rate (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="esi_rate" class="form-control @error('esi_rate') is-invalid @enderror" 
                               value="{{ old('esi_rate') }}">
                        @error('esi_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-sm-4">
                        <label>PF Rate (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="pf_rate" class="form-control @error('pf_rate') is-invalid @enderror" 
                               value="{{ old('pf_rate') }}">
                        @error('pf_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-sm-4">
                        <label>Licensing Cost</label>
                        <input type="number" step="0.01" min="0" name="licensing_cost" class="form-control @error('licensing_cost') is-invalid @enderror" 
                               value="{{ old('licensing_cost') }}">
                        @error('licensing_cost')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-sm-4">
                        <label>Administrative Overhead</label>
                        <input type="number" step="0.01" min="0" name="administrative_overhead" class="form-control @error('administrative_overhead') is-invalid @enderror" 
                               value="{{ old('administrative_overhead') }}">
                        @error('administrative_overhead')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-between mt-3">
                <a href="{{ backpack_url('client') }}" class="btn btn-light">
                    <i class="la la-arrow-left"></i> Cancel
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="la la-save"></i> Save Client
                </button>
            </div>

        </form>
    </div>
</div>
@endsection

@section('after_styles')
    <style>
        .form-group label {
            font-weight: 600;
            color: #374151;
        }
        .card {
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
        }
        .card-body {
            padding: 2rem;
        }
        h5 {
            color: #1f2937;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 0.5rem;
        }
    </style>
@endsection