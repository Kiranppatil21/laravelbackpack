@extends('backpack::layout')

@section('content')
<div class="container mt-5">
    <h1>Signup started</h1>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <p>Your checkout session was created. If you completed payment, you'll receive an email when your subscription is active.</p>
    <p>Checkout Session: <code>{{ $session_id ?? 'N/A' }}</code></p>
</div>
@endsection
