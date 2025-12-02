<!DOCTYPE html>
<html>
<head>
    <title>Simple AJAX Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Simple AJAX Test for Bulk Attendance</h1>
    
    <button id="test-connectivity" class="btn">Test Basic Connectivity</button>
    <button id="test-search" class="btn">Test Search Endpoint</button>
    
    <div id="results" style="margin-top: 20px; padding: 10px; border: 1px solid #ccc; min-height: 200px; font-family: monospace; white-space: pre-wrap;"></div>

    <script>
        function log(message) {
            $('#results').append(new Date().toISOString() + ': ' + message + '\n');
            console.log(message);
        }

        $('#test-connectivity').click(function() {
            log('Testing basic connectivity...');
            
            $.ajax({
                url: '{{ route("admin.bulk-attendance.index") }}',
                method: 'GET',
                success: function(response) {
                    log('✅ Basic connectivity SUCCESS');
                    log('Response type: ' + typeof response);
                    log('Response length: ' + (response ? response.length : 'undefined'));
                },
                error: function(xhr, status, error) {
                    log('❌ Basic connectivity FAILED');
                    log('Status: ' + status + ', Error: ' + error);
                    log('XHR Status: ' + xhr.status);
                    log('Response: ' + xhr.responseText.substring(0, 200));
                }
            });
        });

        $('#test-search').click(function() {
            log('Testing search endpoint...');
            
            const testData = {
                site_id: '1',
                user_type: 'Security Guard',
                month: '2025-11',
                shifts: ['1']
            };
            
            log('Test data: ' + JSON.stringify(testData));
            
            $.ajax({
                url: '{{ route("admin.bulk-attendance.search") }}',
                method: 'POST',
                data: testData,
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                success: function(response) {
                    log('✅ Search endpoint SUCCESS');
                    log('Response: ' + JSON.stringify(response, null, 2));
                },
                error: function(xhr, status, error) {
                    log('❌ Search endpoint FAILED');
                    log('Status: ' + status + ', Error: ' + error);
                    log('XHR Status: ' + xhr.status);
                    log('Content-Type: ' + xhr.getResponseHeader('Content-Type'));
                    log('Response (first 500 chars): ' + xhr.responseText.substring(0, 500));
                }
            });
        });
    </script>
</body>
</html>