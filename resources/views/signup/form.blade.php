@extends('backpack::layout')

@section('content')
<div class="container mt-5">
    <h1>Sign up for a new account</h1>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('signup.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Company / Tenant name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            @error('name')<div class="text-danger">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Domain (for tenant)</label>
            <input type="text" name="domain" class="form-control" value="{{ old('domain') }}" required>
            <div class="form-text">Domain should be unique. Example: acme.localhost or acme.example.com</div>
            @error('domain')<div class="text-danger">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Admin name</label>
            <input type="text" name="admin_name" class="form-control" value="{{ old('admin_name') }}" required>
            @error('admin_name')<div class="text-danger">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Admin email</label>
            <input type="email" name="admin_email" class="form-control" value="{{ old('admin_email') }}" required>
            @error('admin_email')<div class="text-danger">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Price ID (optional)</label>
            <input type="text" name="price_id" class="form-control" value="{{ old('price_id') }}">
            <div class="form-text">Optional: Stripe Price ID to subscribe the tenant to.</div>
            @error('price_id')<div class="text-danger">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Payment gateway</label>
            <select name="gateway" class="form-control">
                <option value="stripe" {{ old('gateway') === 'stripe' ? 'selected' : '' }}>Stripe (default)</option>
                <option value="razorpay" {{ old('gateway') === 'razorpay' ? 'selected' : '' }}>Razorpay</option>
            </select>
            @error('gateway')<div class="text-danger">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Amount (for Razorpay, in INR)</label>
            <input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount') }}">
            <div class="form-text">Optional: Amount in INR to charge during signup when using Razorpay. Example: 49.99</div>
            @error('amount')<div class="text-danger">{{ $message }}</div>@enderror
        </div>

        <button class="btn btn-primary">Start signup & checkout</button>
    </form>
</div>
@endsection
