/**
 * Global CSRF and Session Error Handling for Admin Panel
 */
(function() {
    'use strict';
    
    // Setup CSRF token for all AJAX requests
    const token = document.querySelector('meta[name="csrf-token"]');
    if (token) {
        // Setup CSRF token for jQuery if available
        if (window.$ && $.ajaxSetup) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': token.getAttribute('content')
                }
            });
        }
        
        // Setup CSRF token for Axios if available
        if (window.axios && axios.defaults) {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content');
        }
        
        // Setup CSRF token for native fetch requests
        const originalFetch = window.fetch;
        window.fetch = function(url, options = {}) {
            options.headers = options.headers || {};
            if (!options.headers['X-CSRF-TOKEN'] && !options.headers['x-csrf-token']) {
                options.headers['X-CSRF-TOKEN'] = token.getAttribute('content');
            }
            return originalFetch(url, options);
        };
    }
    
    // Global error handler for CSRF errors
    function handleCsrfError(xhr) {
        if (xhr.status === 419) {
            // Show user-friendly message
            if (window.Swal) {
                Swal.fire({
                    title: 'Session Expired',
                    text: 'Your session has expired. The page will refresh automatically.',
                    icon: 'warning',
                    showConfirmButton: false,
                    timer: 3000
                }).then(() => {
                    window.location.reload();
                });
            } else {
                alert('Session expired. The page will refresh automatically.');
                setTimeout(() => window.location.reload(), 1000);
            }
            return true;
        }
        return false;
    }
    
    // Handle jQuery AJAX errors
    if (window.$ && $(document).ajaxError) {
        $(document).ajaxError(function(event, xhr, settings, thrownError) {
            handleCsrfError(xhr);
        });
    }
    
    // Handle Axios errors
    if (window.axios && axios.interceptors) {
        axios.interceptors.response.use(
            response => response,
            error => {
                if (error.response && error.response.status === 419) {
                    handleCsrfError(error.response);
                }
                return Promise.reject(error);
            }
        );
    }
    
    // Handle native fetch errors
    const originalFetchThen = window.fetch;
    window.fetch = function(url, options = {}) {
        return originalFetchThen(url, options).then(response => {
            if (response.status === 419) {
                handleCsrfError({ status: 419 });
            }
            return response;
        });
    };
    
    // Periodically refresh CSRF token (every 30 minutes)
    setInterval(() => {
        if (token) {
            fetch('/admin/refresh-csrf', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(response => response.json())
              .then(data => {
                  if (data.token) {
                      token.setAttribute('content', data.token);
                      
                      // Update jQuery CSRF token if available
                      if (window.$ && $.ajaxSetup) {
                          $.ajaxSetup({
                              headers: {
                                  'X-CSRF-TOKEN': data.token
                              }
                          });
                      }
                      
                      // Update Axios CSRF token if available
                      if (window.axios && axios.defaults) {
                          axios.defaults.headers.common['X-CSRF-TOKEN'] = data.token;
                      }
                  }
              }).catch(() => {
                  // If token refresh fails, we'll handle it on the next request
                  console.log('CSRF token refresh failed - will handle on next request');
              });
        }
    }, 30 * 60 * 1000); // 30 minutes
    
})();