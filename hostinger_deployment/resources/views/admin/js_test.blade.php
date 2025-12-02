@extends(backpack_view('blank'))

@section('header')
    <section class="container-fluid">
        <h2>
            <span class="text-capitalize">JavaScript Test Page</span>
            <small>Testing for JavaScript errors</small>
        </h2>
    </section>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">JavaScript Error Test</h3>
                </div>
                <div class="card-body">
                    <p>This page tests for JavaScript errors.</p>
                    <div id="error-log"></div>
                    <button id="test-button" class="btn btn-primary">Test Button</button>
                    <div id="test-ajax" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('after_scripts')
<script>
// Capture all JavaScript errors
window.onerror = function(msg, url, lineNo, columnNo, error) {
    const errorDiv = document.getElementById('error-log');
    errorDiv.innerHTML += '<div class="alert alert-danger">JS Error: ' + msg + ' at ' + url + ':' + lineNo + '</div>';
    console.error('JavaScript Error:', msg, url, lineNo, columnNo, error);
    return false;
};

// Capture unhandled promise rejections
window.addEventListener('unhandledrejection', function(event) {
    const errorDiv = document.getElementById('error-log');
    errorDiv.innerHTML += '<div class="alert alert-warning">Promise Rejection: ' + event.reason + '</div>';
    console.error('Unhandled Promise Rejection:', event.reason);
});

document.addEventListener('DOMContentLoaded', function() {
    // Test basic functionality
    document.getElementById('test-button').addEventListener('click', function() {
        alert('Button clicked - no JavaScript errors');
    });
    
    // Test CSRF token availability
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        console.log('CSRF token found:', csrfToken.getAttribute('content'));
    } else {
        console.error('CSRF token not found in page');
        document.getElementById('error-log').innerHTML += '<div class="alert alert-warning">CSRF token not found in page</div>';
    }
    
    console.log('JavaScript test page loaded successfully');
});
</script>
@endsection