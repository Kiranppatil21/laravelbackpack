{{-- Global CSRF fix for all admin pages --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ensure CSRF meta tag exists
    if (!document.querySelector('meta[name="csrf-token"]')) {
        const metaTag = document.createElement('meta');
        metaTag.name = 'csrf-token';
        metaTag.content = '{{ csrf_token() }}';
        document.getElementsByTagName('head')[0].appendChild(metaTag);
    }
    
    // Set up CSRF token for all AJAX requests if jQuery is available
    if (typeof $ !== 'undefined') {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        // Handle 419 errors globally
        $(document).ajaxError(function(event, xhr, settings) {
            if (xhr.status === 419) {
                console.log('CSRF error detected, showing user-friendly message');
                
                // Close any open modals
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const openModals = document.querySelectorAll('.modal.show');
                    openModals.forEach(function(modal) {
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        if (bsModal) {
                            bsModal.hide();
                        }
                    });
                }
                
                // Show user-friendly alert
                alert('Your session has expired. Please refresh the page and try again.');
                
                // Reload the page after user acknowledges
                window.location.reload();
            }
        });
        
        // Ensure all forms have CSRF token
        $(document).on('submit', 'form', function() {
            const form = $(this);
            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            
            if (csrfToken && !form.find('input[name="_token"]').length) {
                form.append('<input type="hidden" name="_token" value="' + csrfToken + '">');
            }
        });
        
        // Handle modal forms
        $(document).on('show.bs.modal', '.modal', function() {
            const modal = $(this);
            const form = modal.find('form');
            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            
            if (form.length && csrfToken) {
                let tokenInput = form.find('input[name="_token"]');
                if (tokenInput.length) {
                    tokenInput.val(csrfToken);
                } else {
                    form.append('<input type="hidden" name="_token" value="' + csrfToken + '">');
                }
            }
        });
    }
    
    // Fallback for browsers without jQuery
    else {
        // Add event listeners for forms
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form.tagName === 'FORM') {
                const metaTag = document.querySelector('meta[name="csrf-token"]');
                const tokenInput = form.querySelector('input[name="_token"]');
                
                if (metaTag && !tokenInput) {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = '_token';
                    hiddenInput.value = metaTag.content;
                    form.appendChild(hiddenInput);
                }
            }
        });
    }
});
</script>