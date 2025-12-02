@extends(backpack_view('blank'))

@php
    $defaultBreadcrumbs = [
      trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
      'Clients' => backpack_url('client'),
      'Edit' => false,
    ];

    $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
    <section class="container-fluid">
      <h2>
        <span class="text-capitalize">Edit Client</span>
        <small>Update client details.</small>
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
        <form method="post" action="{{ route('client.update-custom', $client->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card">
                <div class="card-body row">
                    <!-- Keep most fields same as create view; for brevity include the financial fields here -->

                    <div class="form-group col-sm-4">
                        <label>Billing Rate</label>
                        <input type="number" step="0.01" min="0" name="billing_rate" class="form-control @error('billing_rate') is-invalid @enderror" 
                               value="{{ old('billing_rate', $client->billing_rate) }}">
                        @error('billing_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-sm-4">
                        <label>Salary Cost</label>
                        <input type="number" step="0.01" min="0" name="salary_cost" class="form-control @error('salary_cost') is-invalid @enderror" 
                               value="{{ old('salary_cost', $client->salary_cost) }}">
                        @error('salary_cost')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-sm-4">
                        <label>ESI Rate (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="esi_rate" class="form-control @error('esi_rate') is-invalid @enderror" 
                               value="{{ old('esi_rate', $client->esi_rate) }}">
                        @error('esi_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-sm-4">
                        <label>PF Rate (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="pf_rate" class="form-control @error('pf_rate') is-invalid @enderror" 
                               value="{{ old('pf_rate', $client->pf_rate) }}">
                        @error('pf_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-sm-4">
                        <label>Licensing Cost</label>
                        <input type="number" step="0.01" min="0" name="licensing_cost" class="form-control @error('licensing_cost') is-invalid @enderror" 
                               value="{{ old('licensing_cost', $client->licensing_cost) }}">
                        @error('licensing_cost')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-sm-4">
                        <label>Administrative Overhead</label>
                        <input type="number" step="0.01" min="0" name="administrative_overhead" class="form-control @error('administrative_overhead') is-invalid @enderror" 
                               value="{{ old('administrative_overhead', $client->administrative_overhead) }}">
                        @error('administrative_overhead')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>

            <div class="d-flex justify-content-between mt-3">
                <a href="{{ backpack_url('client') }}" class="btn btn-light">
                    <i class="la la-arrow-left"></i> Cancel
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="la la-save"></i> Update Client
                </button>
            </div>

        </form>
    </div>
</div>
@endsection
