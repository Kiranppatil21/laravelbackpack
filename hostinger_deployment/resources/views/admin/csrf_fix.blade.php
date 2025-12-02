@push('after_styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
/* Ensure modals and popups have proper styling */
.modal-content {
    border-radius: 8px;
}
</style>
@endpush

@push('after_scripts')
<script>
// Global CSRF token setup for all AJAX requests
$(document).ready(function() {
    // Set up CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    // Handle CSRF token refresh
    function refreshCsrfToken() {
        $.get('/admin/refresh-csrf')
            .done(function(data) {
                if (data.token) {
                    $('meta[name="csrf-token"]').attr('content', data.token);
                    $('input[name="_token"]').val(data.token);
                    console.log('CSRF token refreshed');
                }
            })
            .fail(function() {
                console.warn('Could not refresh CSRF token');
            });
    }
    
    // Handle 419 errors globally
    $(document).ajaxError(function(event, xhr, settings) {
        if (xhr.status === 419) {
            // Try to refresh token and retry once
            refreshCsrfToken();
            
            // Show user-friendly message
            if (typeof crud !== 'undefined' && crud.modal) {
                crud.modal.hide();
            }
            
            new Noty({
                type: "error",
                text: "Your session has expired. Please refresh the page and try again."
            }).show();
            
            // Optionally reload the page after a delay
            setTimeout(function() {
                window.location.reload();
            }, 3000);
        }
    });
    
    // Refresh CSRF token every 10 minutes
    setInterval(refreshCsrfToken, 600000); // 10 minutes
    
    // Ensure forms have CSRF token
    $('form').each(function() {
        if (!$(this).find('input[name="_token"]').length) {
            $(this).append('<input type="hidden" name="_token" value="' + $('meta[name="csrf-token"]').attr('content') + '">');
        }
    });
    
    // Handle modal forms specifically
    $(document).on('show.bs.modal', '.modal', function() {
        var modal = $(this);
        var form = modal.find('form');
        
        // Ensure modal forms have fresh CSRF tokens
        if (form.length) {
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            var tokenInput = form.find('input[name="_token"]');
            
            if (tokenInput.length) {
                tokenInput.val(csrfToken);
            } else {
                form.append('<input type="hidden" name="_token" value="' + csrfToken + '">');
            }
        }
    });
    
    // Handle Backpack CRUD operations
    if (typeof crud !== 'undefined') {
        // Override CRUD create operation
        var originalCreate = crud.create;
        crud.create = function(button) {
            // Refresh CSRF token before creating
            refreshCsrfToken();
            setTimeout(function() {
                if (originalCreate) {
                    originalCreate.call(crud, button);
                }
            }, 100);
        };
        
        // Override CRUD edit operation
        var originalEdit = crud.edit;
        crud.edit = function(button) {
            // Refresh CSRF token before editing
            refreshCsrfToken();
            setTimeout(function() {
                if (originalEdit) {
                    originalEdit.call(crud, button);
                }
            }, 100);
        };
    }
});
</script>
@endpush