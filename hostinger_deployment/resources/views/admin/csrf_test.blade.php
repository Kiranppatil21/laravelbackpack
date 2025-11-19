@extends(backpack_view('blank'))

{{-- Include global AJAX CSRF fix --}}
@include('admin.global_ajax_csrf_fix')

@section('header')
    <section class="container-fluid">
        <h2>CSRF Test Page</h2>
    </section>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">CSRF Token Test</h3>
                </div>
                <div class="card-body">
                    <p><strong>Current CSRF Token:</strong> <code id="csrf-token-display">{{ csrf_token() }}</code></p>
                    <p><strong>Session ID:</strong> <code>{{ session()->getId() }}</code></p>
                    
                    <div class="mb-3">
                        <button id="test-ajax" class="btn btn-primary">Test AJAX Request</button>
                        <button id="test-search" class="btn btn-secondary">Test Attendance Search</button>
                    </div>
                    
                    <form id="csrf-test-form" method="POST" action="{{ route('admin.csrf.test.post') }}">
                        @csrf
                        <button type="submit" class="btn btn-success">Test Form Submission</button>
                    </form>
                    
                    <div id="test-result" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('after_scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded, CSRF token:', window.csrfToken);
    
    // Test AJAX request
    document.getElementById('test-ajax').addEventListener('click', function() {
        showResult('Testing AJAX...', 'info');
        
        $.ajax({
            url: '{{ route("admin.csrf.test.post") }}',
            method: 'POST',
            data: { test: 'ajax' },
            success: function(data) {
                showResult('✅ AJAX Success: ' + data.message, 'success');
            },
            error: function(xhr) {
                showResult('❌ AJAX Error: ' + xhr.status + ' - ' + xhr.statusText, 'danger');
            }
        });
    });
    
    // Test attendance search
    document.getElementById('test-search').addEventListener('click', function() {
        showResult('Testing Attendance Search...', 'info');
        
        $.ajax({
            url: '/admin/attendance/search',
            method: 'POST',
            data: { q: 'test' },
            success: function(data) {
                showResult('✅ Search Success: Data received', 'success');
            },
            error: function(xhr) {
                showResult('❌ Search Error: ' + xhr.status + ' - ' + xhr.statusText, 'danger');
            }
        });
    });
    
    // Handle form submission
    document.getElementById('csrf-test-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.ok) {
                return response.json();
            }
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        })
        .then(data => {
            showResult('✅ Form Success: ' + data.message, 'success');
        })
        .catch(error => {
            showResult('❌ Form Error: ' + error.message, 'danger');
        });
    });
    
    function showResult(message, type) {
        document.getElementById('test-result').innerHTML = `
            <div class="alert alert-${type}">
                ${message}
            </div>
        `;
    }
});
</script>
@endsection