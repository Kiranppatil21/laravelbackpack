@php
    $status = $entry->status ?? 'inactive';
@endphp

<button type="button" 
        class="btn btn-sm btn-success reactivate-employee-btn" 
        data-employee-id="{{ $entry->getKey() }}"
        title="Reactivate Employee">
    <i class="la la-check-circle"></i> Reactivate
</button>

@push('after_scripts')
<script>
$(document).ready(function() {
    // Reactivate employee
    $(document).on('click', '.reactivate-employee-btn', function(e) {
        e.preventDefault();
        
        const btn = $(this);
        const employeeId = btn.data('employee-id');
        
        if (!confirm('Are you sure you want to reactivate this employee?')) {
            return;
        }
        
        // Show loading
        btn.prop('disabled', true);
        const originalHtml = btn.html();
        btn.html('<i class="la la-spinner la-spin"></i> Processing...');
        
        // Make AJAX request
        $.ajax({
            url: '{{ url(config('backpack.base.route_prefix', 'admin').'/employee') }}/' + employeeId + '/toggle-status',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    new Noty({
                        type: 'success',
                        text: 'Employee reactivated successfully'
                    }).show();
                    
                    // Reload the page
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } else {
                    new Noty({
                        type: 'error',
                        text: 'Failed to reactivate employee'
                    }).show();
                    
                    btn.prop('disabled', false);
                    btn.html(originalHtml);
                }
            },
            error: function(xhr) {
                new Noty({
                    type: 'error',
                    text: 'Error: ' + (xhr.responseJSON?.message || 'Something went wrong')
                }).show();
                
                btn.prop('disabled', false);
                btn.html(originalHtml);
            }
        });
    });
});
</script>
@endpush
