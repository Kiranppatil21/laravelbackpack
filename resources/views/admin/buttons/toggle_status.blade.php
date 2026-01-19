@php
    $status = $entry->status ?? 'active';
    $isActive = $status === 'active';
@endphp

<button type="button" 
        class="btn btn-sm {{ $isActive ? 'btn-danger' : 'btn-success' }} toggle-status-btn" 
        data-employee-id="{{ $entry->getKey() }}"
        title="{{ $isActive ? 'Deactivate' : 'Activate' }} Employee">
    <i class="la {{ $isActive ? 'la-ban' : 'la-check-circle' }}"></i> 
    {{ $isActive ? 'Deactivate' : 'Activate' }}
</button>

@push('after_scripts')
<script>
$(document).ready(function() {
    // Toggle employee status
    $(document).on('click', '.toggle-status-btn', function(e) {
        e.preventDefault();
        
        const btn = $(this);
        const employeeId = btn.data('employee-id');
        const currentStatus = btn.hasClass('btn-danger') ? 'active' : 'inactive';
        const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
        const action = newStatus === 'active' ? 'activate' : 'deactivate';
        
        if (!confirm(`Are you sure you want to ${action} this employee?`)) {
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
                        text: response.message
                    }).show();
                    
                    // Reload the page to reflect changes
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } else {
                    // Show error
                    new Noty({
                        type: 'error',
                        text: 'Failed to update employee status'
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
