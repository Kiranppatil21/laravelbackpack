{{-- Custom dropdown styles and scripts --}}
<link rel="stylesheet" href="{{ asset('css/custom-dropdown.css') }}">

{{-- Add custom dropdown styles inline as backup --}}
<style>
/* Ensure dropdown functionality works */
.nav-dropdown {
    position: relative;
}

.nav-dropdown .nav-dropdown-items {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    min-width: 200px;
    z-index: 1050;
    padding: 0.5rem 0;
}

.nav-dropdown:hover .nav-dropdown-items {
    display: block;
}

.nav-dropdown .nav-dropdown-items .nav-link {
    padding: 8px 16px;
    color: #495057;
    display: flex;
    align-items: center;
    transition: all 0.2s ease;
}

.nav-dropdown .nav-dropdown-items .nav-link:hover {
    background-color: #f8f9fa;
    color: #007bff;
    text-decoration: none;
    padding-left: 20px;
}

.nav-dropdown-toggle::after {
    content: '▼';
    font-size: 0.8em;
    margin-left: 8px;
    transition: transform 0.3s ease;
}

.nav-dropdown:hover .nav-dropdown-toggle::after {
    transform: rotate(180deg);
}

/* Mobile responsive */
@media (max-width: 768px) {
    .nav-dropdown .nav-dropdown-items {
        position: static;
        box-shadow: none;
        border: none;
        background: transparent;
    }
}
</style>

<script>
// Ensure jQuery is loaded before our script
document.addEventListener('DOMContentLoaded', function() {
    // Load custom dropdown script
    const script = document.createElement('script');
    script.src = '{{ asset('js/custom-dropdown.js') }}';
    script.async = true;
    document.head.appendChild(script);
});
</script>