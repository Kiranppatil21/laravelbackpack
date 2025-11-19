{{-- This file is used for menu items by any Backpack v6 theme --}}

{{-- Include custom dropdown assets --}}
@push('after_styles')
<link rel="stylesheet" href="{{ asset('css/custom-dropdown.css') }}">
<style>
/* Enhanced dropdown styles for Backpack theme */
.nav-dropdown { 
    position: relative !important; 
    display: inline-block !important;
}

.nav-dropdown .nav-dropdown-items { 
    display: none !important; 
    position: absolute !important; 
    top: calc(100% + 5px) !important; 
    left: 0 !important; 
    background: #ffffff !important; 
    border: 1px solid #e9ecef !important; 
    border-radius: 0.5rem !important; 
    box-shadow: 0 8px 16px 0 rgba(0,0,0,0.1) !important; 
    min-width: 220px !important; 
    max-width: 300px !important;
    z-index: 1050 !important; 
    padding: 0.5rem 0 !important; 
    margin: 0 !important;
    list-style: none !important;
    opacity: 0 !important;
    transform: translateY(-10px) !important;
    transition: all 0.3s ease !important;
    pointer-events: none !important;
}

.nav-dropdown:hover .nav-dropdown-items { 
    display: block !important; 
    opacity: 1 !important;
    transform: translateY(0) !important;
    pointer-events: auto !important;
}

.nav-dropdown .nav-dropdown-items::before {
    content: '' !important;
    position: absolute !important;
    top: -8px !important;
    left: 20px !important;
    border-left: 8px solid transparent !important;
    border-right: 8px solid transparent !important;
    border-bottom: 8px solid #ffffff !important;
    z-index: 1051 !important;
}

.nav-dropdown .nav-dropdown-items::after {
    content: '' !important;
    position: absolute !important;
    top: -9px !important;
    left: 20px !important;
    border-left: 8px solid transparent !important;
    border-right: 8px solid transparent !important;
    border-bottom: 8px solid #e9ecef !important;
    z-index: 1050 !important;
}

.nav-dropdown .nav-dropdown-items .nav-item {
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
    width: 100% !important;
}

.nav-dropdown .nav-dropdown-items .nav-link { 
    padding: 12px 20px !important; 
    color: #495057 !important; 
    display: flex !important; 
    align-items: center !important; 
    transition: all 0.2s ease !important;
    text-decoration: none !important;
    border-radius: 0 !important;
    margin: 0 !important;
    background: transparent !important;
    white-space: nowrap !important;
    font-size: 14px !important;
    font-weight: 400 !important;
}

.nav-dropdown .nav-dropdown-items .nav-link:hover { 
    background: linear-gradient(90deg, #f8f9fa 0%, #e3f2fd 100%) !important; 
    color: #007bff !important; 
    text-decoration: none !important; 
    transform: translateX(5px) !important;
    border-left: 3px solid #007bff !important;
    padding-left: 17px !important;
}

.nav-dropdown .nav-dropdown-items .nav-link .nav-icon {
    margin-right: 12px !important;
    width: 18px !important;
    font-size: 16px !important;
    text-align: center !important;
    color: inherit !important;
}

.nav-dropdown-toggle::after { 
    content: '▼' !important; 
    font-size: 0.75em !important; 
    margin-left: 8px !important; 
    transition: transform 0.3s ease !important; 
    display: inline-block !important;
}

.nav-dropdown:hover .nav-dropdown-toggle::after { 
    transform: rotate(180deg) !important; 
}

.nav-dropdown .nav-dropdown-toggle {
    position: relative !important;
}

/* Ensure proper spacing between dropdown and parent */
.nav-item.nav-dropdown {
    margin-right: 0 !important;
}

/* Mobile responsive */
@media (max-width: 768px) { 
    .nav-dropdown .nav-dropdown-items { 
        position: static !important; 
        box-shadow: none !important; 
        border: none !important; 
        background: rgba(248, 249, 250, 0.95) !important; 
        margin-left: 20px !important;
        border-left: 2px solid #007bff !important;
        transform: none !important;
        opacity: 1 !important;
        transition: none !important;
    }
    
    .nav-dropdown .nav-dropdown-items::before,
    .nav-dropdown .nav-dropdown-items::after {
        display: none !important;
    }
    
    .nav-dropdown .nav-dropdown-items .nav-link {
        padding: 10px 16px !important;
        padding-left: 24px !important;
    }
    
    .nav-dropdown .nav-dropdown-items .nav-link:hover {
        padding-left: 28px !important;
        transform: none !important;
        border-left: none !important;
    }
}

/* Dark theme support */
.dark .nav-dropdown .nav-dropdown-items {
    background: #374151 !important;
    border-color: #4B5563 !important;
}

.dark .nav-dropdown .nav-dropdown-items::before {
    border-bottom-color: #374151 !important;
}

.dark .nav-dropdown .nav-dropdown-items::after {
    border-bottom-color: #4B5563 !important;
}

.dark .nav-dropdown .nav-dropdown-items .nav-link {
    color: #D1D5DB !important;
}

.dark .nav-dropdown .nav-dropdown-items .nav-link:hover {
    background: linear-gradient(90deg, #4B5563 0%, #1E3A8A 100%) !important;
    color: #60A5FA !important;
    border-left-color: #60A5FA !important;
}

/* Animation keyframes */
@keyframes dropdownFadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.nav-dropdown:hover .nav-dropdown-items {
    animation: dropdownFadeIn 0.3s ease forwards !important;
}
</style>
@endpush

@push('after_scripts')
<script src="{{ asset('js/custom-dropdown.js') }}"></script>
<script>
// Immediate dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing dropdown functionality...');
    
    const dropdowns = document.querySelectorAll('.nav-dropdown');
    console.log('Found', dropdowns.length, 'dropdown elements');
    
    dropdowns.forEach(function(dropdown, index) {
        const toggle = dropdown.querySelector('.nav-dropdown-toggle');
        const items = dropdown.querySelector('.nav-dropdown-items');
        
        if (!toggle || !items) {
            console.log('Missing toggle or items for dropdown', index);
            return;
        }
        
        console.log('Setting up dropdown', index);
        
        // Ensure dropdown is positioned relative to its parent
        dropdown.style.position = 'relative';
        dropdown.style.display = 'inline-block';
        
        // Set initial styles for dropdown items
        items.style.display = 'none';
        items.style.position = 'absolute';
        items.style.top = 'calc(100% + 5px)';
        items.style.left = '0';
        items.style.zIndex = '1050';
        items.style.minWidth = '220px';
        items.style.maxWidth = '300px';
        items.style.background = '#ffffff';
        items.style.border = '1px solid #e9ecef';
        items.style.borderRadius = '0.5rem';
        items.style.boxShadow = '0 8px 16px 0 rgba(0,0,0,0.1)';
        items.style.padding = '0.5rem 0';
        items.style.opacity = '0';
        items.style.transform = 'translateY(-10px)';
        items.style.transition = 'all 0.3s ease';
        items.style.pointerEvents = 'none';
        
        let hoverTimeout;
        let isHovering = false;
        
        // Mouse events with improved timing
        dropdown.addEventListener('mouseenter', function() {
            clearTimeout(hoverTimeout);
            isHovering = true;
            console.log('Showing dropdown', index);
            
            // Hide other dropdowns first
            dropdowns.forEach(function(otherDropdown, otherIndex) {
                if (otherDropdown !== dropdown) {
                    const otherItems = otherDropdown.querySelector('.nav-dropdown-items');
                    const otherToggle = otherDropdown.querySelector('.nav-dropdown-toggle');
                    if (otherItems) {
                        otherItems.style.display = 'none';
                        otherItems.style.opacity = '0';
                        otherItems.style.transform = 'translateY(-10px)';
                        otherItems.style.pointerEvents = 'none';
                        if (otherToggle) {
                            otherToggle.setAttribute('aria-expanded', 'false');
                        }
                    }
                }
            });
            
            // Calculate position to ensure dropdown appears below the tab
            const rect = dropdown.getBoundingClientRect();
            const viewportWidth = window.innerWidth;
            
            // Adjust horizontal position if dropdown would go off-screen
            if (rect.left + 220 > viewportWidth) {
                items.style.left = 'auto';
                items.style.right = '0';
            } else {
                items.style.left = '0';
                items.style.right = 'auto';
            }
            
            // Show dropdown with animation
            items.style.display = 'block';
            items.style.pointerEvents = 'auto';
            
            // Trigger animation
            requestAnimationFrame(function() {
                items.style.opacity = '1';
                items.style.transform = 'translateY(0)';
            });
            
            toggle.setAttribute('aria-expanded', 'true');
        });
        
        dropdown.addEventListener('mouseleave', function() {
            isHovering = false;
            hoverTimeout = setTimeout(function() {
                if (!isHovering) {
                    console.log('Hiding dropdown', index);
                    items.style.opacity = '0';
                    items.style.transform = 'translateY(-10px)';
                    items.style.pointerEvents = 'none';
                    
                    setTimeout(function() {
                        if (!isHovering) {
                            items.style.display = 'none';
                        }
                    }, 300);
                    
                    toggle.setAttribute('aria-expanded', 'false');
                }
            }, 150);
        });
        
        // Keep dropdown open when hovering over items
        items.addEventListener('mouseenter', function() {
            clearTimeout(hoverTimeout);
            isHovering = true;
        });
        
        items.addEventListener('mouseleave', function() {
            isHovering = false;
            hoverTimeout = setTimeout(function() {
                if (!isHovering) {
                    items.style.opacity = '0';
                    items.style.transform = 'translateY(-10px)';
                    items.style.pointerEvents = 'none';
                    
                    setTimeout(function() {
                        if (!isHovering) {
                            items.style.display = 'none';
                        }
                    }, 300);
                    
                    toggle.setAttribute('aria-expanded', 'false');
                }
            }, 150);
        });
        
        // Style dropdown links
        const links = items.querySelectorAll('.nav-link');
        links.forEach(function(link) {
            link.style.padding = '12px 20px';
            link.style.display = 'flex';
            link.style.alignItems = 'center';
            link.style.color = '#495057';
            link.style.textDecoration = 'none';
            link.style.transition = 'all 0.2s ease';
            link.style.borderRadius = '0';
            link.style.margin = '0';
            link.style.background = 'transparent';
            link.style.whiteSpace = 'nowrap';
            link.style.fontSize = '14px';
            link.style.fontWeight = '400';
            
            link.addEventListener('mouseenter', function() {
                this.style.background = 'linear-gradient(90deg, #f8f9fa 0%, #e3f2fd 100%)';
                this.style.color = '#007bff';
                this.style.transform = 'translateX(5px)';
                this.style.borderLeft = '3px solid #007bff';
                this.style.paddingLeft = '17px';
            });
            
            link.addEventListener('mouseleave', function() {
                this.style.background = 'transparent';
                this.style.color = '#495057';
                this.style.transform = 'translateX(0)';
                this.style.borderLeft = 'none';
                this.style.paddingLeft = '20px';
            });
        });
        
        // Add ARIA attributes
        toggle.setAttribute('role', 'button');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-haspopup', 'true');
        
        if (items) {
            items.setAttribute('role', 'menu');
            items.setAttribute('aria-hidden', 'true');
        }
    });
    
    // Close dropdowns on window resize
    window.addEventListener('resize', function() {
        dropdowns.forEach(function(dropdown) {
            const items = dropdown.querySelector('.nav-dropdown-items');
            const toggle = dropdown.querySelector('.nav-dropdown-toggle');
            if (items) {
                items.style.display = 'none';
                items.style.opacity = '0';
                items.style.transform = 'translateY(-10px)';
                items.style.pointerEvents = 'none';
                if (toggle) {
                    toggle.setAttribute('aria-expanded', 'false');
                }
            }
        });
    });
    
    console.log('Dropdown initialization complete');
});
</script>
@endpush

<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('dashboard') }}">
        <i class="la la-home nav-icon"></i>
        {{ trans('backpack::base.dashboard') }}
    </a>
</li>

{{-- SUPER ADMIN - Full System Access --}}
@if(backpack_user() && backpack_user()->hasRole('Super Admin'))
<li class="nav-item nav-dropdown">
    <a class="nav-link nav-dropdown-toggle" href="#" role="button" aria-expanded="false">
        <i class="nav-icon la la-users"></i>
        User Management
    </a>
    <ul class="nav-dropdown-items">
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('user') }}">
                <i class="nav-icon la la-user"></i>
                <span>Users</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('roles') }}">
                <i class="nav-icon la la-id-badge"></i>
                <span>Roles</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('permissions') }}">
                <i class="nav-icon la la-key"></i>
                <span>Permissions</span>
            </a>
        </li>
    </ul>
</li>

<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('agency') }}">
        <i class="nav-icon la la-building"></i>
        Agencies
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('client') }}">
        <i class="nav-icon la la-handshake"></i>
        Clients
    </a>
</li>

<li class="nav-item nav-dropdown">
    <a class="nav-link nav-dropdown-toggle" href="#" role="button" aria-expanded="false">
        <i class="nav-icon la la-users"></i>
        Employee Management
    </a>
    <ul class="nav-dropdown-items">
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('employee') }}">
                <i class="nav-icon la la-users"></i>
                <span>All Employees</span>
            </a>
        </li>
    </ul>
</li>

<li class="nav-item nav-dropdown">
    <a class="nav-link nav-dropdown-toggle" href="#" role="button" aria-expanded="false">
        <i class="nav-icon la la-clock"></i>
        Attendance Management
    </a>
    <ul class="nav-dropdown-items">
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('attendance') }}">
                <i class="nav-icon la la-list"></i>
                <span>Attendance Records</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('bulk-attendance') }}">
                <i class="nav-icon la la-calendar"></i>
                <span>Bulk Attendance</span>
            </a>
        </li>
    </ul>
</li>

<li class="nav-item nav-dropdown">
    <a class="nav-link nav-dropdown-toggle" href="#" role="button" aria-expanded="false">
        <i class="nav-icon la la-money"></i>
        Payroll & Finance
    </a>
    <ul class="nav-dropdown-items">
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('payroll') }}">
                <i class="nav-icon la la-money"></i>
                <span>Payroll Management</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('client-invoice') }}">
                <i class="nav-icon la la-file-invoice-dollar"></i>
                <span>Client Invoices</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('invoice') }}">
                <i class="nav-icon la la-file-invoice"></i>
                <span>Invoices</span>
            </a>
        </li>
    </ul>
</li>
@endif

{{-- AGENCY OWNER - Business Management --}}
@if(backpack_user() && backpack_user()->hasRole('Agency Owner'))
<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('agency') }}">
        <i class="nav-icon la la-building"></i>
        My Agency
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('client') }}">
        <i class="nav-icon la la-handshake"></i>
        My Clients
    </a>
</li>

<li class="nav-item nav-dropdown">
    <a class="nav-link nav-dropdown-toggle" href="#" role="button" aria-expanded="false">
        <i class="nav-icon la la-users"></i>
        Staff Management
    </a>
    <ul class="nav-dropdown-items">
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('employee') }}">
                <i class="nav-icon la la-users"></i>
                <span>My Guards & Staff</span>
            </a>
        </li>
    </ul>
</li>

<li class="nav-item nav-dropdown">
    <a class="nav-link nav-dropdown-toggle" href="#" role="button" aria-expanded="false">
        <i class="nav-icon la la-clock"></i>
        Attendance Management
    </a>
    <ul class="nav-dropdown-items">
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('attendance') }}">
                <i class="nav-icon la la-list"></i>
                <span>Staff Attendance</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('bulk-attendance') }}">
                <i class="nav-icon la la-calendar"></i>
                <span>Bulk Attendance</span>
            </a>
        </li>
    </ul>
</li>

<li class="nav-item nav-dropdown">
    <a class="nav-link nav-dropdown-toggle" href="#" role="button" aria-expanded="false">
        <i class="nav-icon la la-money"></i>
        Financial Management
    </a>
    <ul class="nav-dropdown-items">
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('payroll') }}">
                <i class="nav-icon la la-money"></i>
                <span>Staff Payroll</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('client-invoice') }}">
                <i class="nav-icon la la-file-invoice-dollar"></i>
                <span>Client Invoices</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('invoice') }}">
                <i class="nav-icon la la-file-invoice"></i>
                <span>General Invoices</span>
            </a>
        </li>
    </ul>
</li>
@endif

{{-- HR - People Management --}}
@if(backpack_user() && backpack_user()->hasRole('HR'))
<li class="nav-item nav-dropdown">
    <a class="nav-link nav-dropdown-toggle" href="#" role="button" aria-expanded="false">
        <i class="nav-icon la la-users"></i>
        Employee Management
    </a>
    <ul class="nav-dropdown-items">
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('employee') }}">
                <i class="nav-icon la la-users"></i>
                <span>All Employees</span>
            </a>
        </li>
    </ul>
</li>

<li class="nav-item nav-dropdown">
    <a class="nav-link nav-dropdown-toggle" href="#" role="button" aria-expanded="false">
        <i class="nav-icon la la-clock"></i>
        Attendance Management
    </a>
    <ul class="nav-dropdown-items">
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('attendance') }}">
                <i class="nav-icon la la-list"></i>
                <span>Attendance Records</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('bulk-attendance') }}">
                <i class="nav-icon la la-calendar"></i>
                <span>Bulk Attendance</span>
            </a>
        </li>
    </ul>
</li>

<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('payroll') }}">
        <i class="nav-icon la la-money"></i>
        Payroll Management
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('agency') }}">
        <i class="nav-icon la la-building"></i>
        Agency Information
    </a>
</li>
@endif

{{-- CLIENT - Limited View --}}
@if(backpack_user() && backpack_user()->hasRole('Client'))
<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('employee') }}">
        <i class="nav-icon la la-user-shield"></i>
        Assigned Guards
    </a>
</li>

<li class="nav-item nav-dropdown">
    <a class="nav-link nav-dropdown-toggle" href="#" role="button" aria-expanded="false">
        <i class="nav-icon la la-clock"></i>
        Guard Attendance
    </a>
    <ul class="nav-dropdown-items">
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('attendance') }}">
                <i class="nav-icon la la-list"></i>
                <span>Attendance Records</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('bulk-attendance') }}">
                <i class="nav-icon la la-calendar"></i>
                <span>View Bulk Attendance</span>
            </a>
        </li>
    </ul>
</li>

<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('invoice') }}">
        <i class="nav-icon la la-file-invoice-dollar"></i>
        My Invoices
    </a>
</li>
@endif

{{-- GUARD/EMPLOYEE - Personal View --}}
@if(backpack_user() && backpack_user()->hasRole('Guard/Employee'))
<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('attendance') }}">
        <i class="nav-icon la la-clock"></i>
        My Attendance
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('payroll') }}">
        <i class="nav-icon la la-money"></i>
        My Payslips
    </a>
</li>
@endif

{{-- VISITOR - Check-in System --}}
@if(backpack_user() && backpack_user()->hasRole('Visitor'))
<li class="nav-item">
    <a class="nav-link" href="#visitor-checkin">
        <i class="nav-icon la la-sign-in-alt"></i>
        Check In/Out
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="#visitor-history">
        <i class="nav-icon la la-history"></i>
        My Visit History
    </a>
</li>
@endif

{{-- POLICE - Oversight --}}
@if(backpack_user() && backpack_user()->hasRole('Police'))
<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('agency') }}">
        <i class="nav-icon la la-building"></i>
        Licensed Agencies
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('employee') }}">
        <i class="nav-icon la la-users"></i>
        Security Personnel
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="#compliance-reports">
        <i class="nav-icon la la-file-alt"></i>
        Compliance Reports
    </a>
</li>
@endif

<x-backpack::menu-item title="Client invoices" icon="la la-question" :link="backpack_url('client-invoice')" />