{{-- Global CSRF and AJAX fix for all Backpack pages --}}
@push('after_scripts')
<script>
// Ensure CSRF token is available globally
window.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

// Set up CSRF token for all jQuery AJAX requests
if (typeof $ !== 'undefined') {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': window.csrfToken
        }
    });
    
    // Override jQuery's AJAX to always include CSRF token
    const originalAjax = $.ajax;
    $.ajax = function(options) {
        if (typeof options === 'string') {
            options = {url: options};
        }
        
        options = options || {};
        options.headers = options.headers || {};
        
        if (!options.headers['X-CSRF-TOKEN'] && !options.headers['X-CSRF-Token']) {
            options.headers['X-CSRF-TOKEN'] = window.csrfToken;
        }
        
        return originalAjax.call(this, options);
    };
    
    // Ensure all forms have CSRF token
    $(document).ready(function() {
        $('form').each(function() {
            const form = $(this);
            if (!form.find('input[name="_token"]').length) {
                form.append('<input type="hidden" name="_token" value="' + window.csrfToken + '">');
            }
        });
    });
    
    // Handle DataTables AJAX requests specifically (Backpack uses DataTables for list views)
    if (typeof $.fn.dataTable !== 'undefined') {
        $.extend(true, $.fn.dataTable.defaults, {
            ajax: {
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', window.csrfToken);
                },
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken
                }
            }
        });
    }
}

// Native fetch API override for non-jQuery AJAX
if (typeof window.fetch !== 'undefined') {
    const originalFetch = window.fetch;
    window.fetch = function(url, options) {
        options = options || {};
        options.headers = options.headers || {};
        
        if (options.method && options.method.toLowerCase() !== 'get') {
            if (!options.headers['X-CSRF-TOKEN'] && !options.headers['X-CSRF-Token']) {
                options.headers['X-CSRF-TOKEN'] = window.csrfToken;
            }
        }
        
        return originalFetch.call(this, url, options);
    };
}

console.log('CSRF token configured:', window.csrfToken);
</script>
@endpush