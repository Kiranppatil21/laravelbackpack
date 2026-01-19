{{-- This file is used for menu items by any Backpack v6 theme --}}

<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('dashboard') }}">
        <i class="la la-home nav-icon"></i>
        {{ trans('backpack::base.dashboard') }}
    </a>
</li>

{{-- SUPER ADMIN - Full System Access --}}
@if(backpack_user() && backpack_user()->hasRole('Super Admin'))
<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('agency') }}">
        <i class="nav-icon la la-building"></i>
        Agencies
    </a>
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
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('bulk-attendance/view') }}">
                <i class="nav-icon la la-table"></i>
                <span>All Bulk Records</span>
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
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('bulk-attendance/view') }}">
                <i class="nav-icon la la-table"></i>
                <span>All Bulk Records</span>
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
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('bulk-attendance/view') }}">
                <i class="nav-icon la la-table"></i>
                <span>All Bulk Records</span>
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

@unless(backpack_user() && backpack_user()->hasRole('Super Admin'))
<x-backpack::menu-item title="Client invoices" icon="la la-question" :link="backpack_url('client-invoice')" />
@endunless

{{-- Include Horizontal Header Menu Styles --}}
@push('after_styles')
<style>
/* ========================================
   HORIZONTAL HEADER NAVIGATION STYLES
   ======================================== */

/* Header Container */
.navbar-expand-lg.top {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1030;
    background: var(--tblr-bg-surface);
    border-bottom: 1px solid var(--tblr-border-color);
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.navbar-expand-lg.top .navbar {
    padding: 0;
    min-height: 60px;
}

.navbar-expand-lg.top .container-fluid {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 1rem;
}

/* Body Padding for Fixed Header */
body {
    padding-top: 60px;
    margin-left: 0 !important;
}



/* Main Navigation */
.navbar-nav {
    display: flex;
    flex-direction: row;
    align-items: center;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 0.25rem;
}

/* Navigation Items */
.navbar-nav > .nav-item {
    position: relative;
}

.navbar-nav > .nav-item > .nav-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    color: var(--tblr-body-color);
    text-decoration: none;
    border-radius: 0.25rem;
    transition: all 0.15s ease;
    white-space: nowrap;
}

.navbar-nav > .nav-item > .nav-link:hover {
    background-color: var(--tblr-bg-surface-secondary);
    color: var(--tblr-primary);
}

.navbar-nav > .nav-item > .nav-link.active {
    background-color: var(--tblr-primary);
    color: #fff;
}

/* Navigation Icons */
.nav-icon {
    font-size: 1.25rem;
    width: 1.25rem;
    text-align: center;
}

/* Dropdown Navigation */
.nav-item.nav-dropdown {
    position: relative;
}

.nav-dropdown-toggle {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.nav-dropdown-toggle::after {
    content: "";
    margin-left: 0.25rem;
    border-top: 0.3em solid;
    border-right: 0.3em solid transparent;
    border-left: 0.3em solid transparent;
}

/* Dropdown Menu */
.navbar-nav .dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    min-width: 220px;
    margin-top: 0;
    background: var(--tblr-bg-surface);
    border: 1px solid var(--tblr-border-color);
    border-radius: 0.375rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    z-index: 1031;
    display: none;
}

.navbar-nav .dropdown-menu.show {
    display: block;
}

/* Position dropdown relative to parent */
.navbar-nav > .nav-item.nav-dropdown {
    position: relative;
}

.navbar-nav > .nav-item.nav-dropdown > .dropdown-menu {
    left: 0;
    right: auto;
}

.navbar-nav .dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    color: var(--tblr-body-color);
    text-decoration: none;
    transition: all 0.15s ease;
}

.navbar-nav .dropdown-item:hover {
    background-color: var(--tblr-bg-surface-secondary);
    color: var(--tblr-primary);
}

/* Nested Dropdowns (Dropend) */
.dropend {
    position: relative;
}

.dropend > .dropdown-toggle::after {
    margin-left: auto;
    border-top: 0.3em solid transparent;
    border-bottom: 0.3em solid transparent;
    border-left: 0.3em solid;
}

.dropend .dropdown-menu {
    top: 0;
    left: 100%;
    margin-top: 0;
    margin-left: 0.25rem;
}

/* Dropdown Headers and Separators */
.dropdown-header {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--tblr-text-muted);
    text-transform: uppercase;
}

/* Right Side Navigation */
.navbar-nav.flex-shrink-0 {
    margin-left: auto;
}

/* Navbar Toggler (Mobile Hamburger) */
.navbar-toggler {
    border: none;
    padding: 0.25rem 0.5rem;
    background: transparent;
}

.navbar-toggler:focus {
    box-shadow: none;
}

.navbar-toggler-icon {
    width: 1.5em;
    height: 1.5em;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(0, 0, 0, 0.55)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}

[data-bs-theme="dark"] .navbar-toggler-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 255, 255, 0.75)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}

/* Mobile Brand */
.navbar-brand {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--tblr-body-color);
    text-decoration: none;
}

/* Mobile Toggle Button */
@media (max-width: 991.98px) {
    .navbar-expand-lg.top {
        padding: 0;
    }

    .navbar-expand-lg.top > .container-fluid {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 1rem;
    }

    .navbar-expand-lg.top .navbar-collapse {
        position: fixed;
        top: 60px;
        left: 0;
        right: 0;
        bottom: 0;
        background: var(--tblr-bg-surface);
        padding: 1rem;
        overflow-y: auto;
        border-top: 1px solid var(--tblr-border-color);
    }

    .navbar-expand-lg.top .navbar-collapse:not(.show) {
        display: none;
    }

    .navbar-nav {
        flex-direction: column;
        align-items: flex-start;
        width: 100%;
    }

    .navbar-nav > .nav-item {
        width: 100%;
    }

    .navbar-nav > .nav-item > .nav-link {
        width: 100%;
        padding: 0.75rem 1rem;
    }

    .dropdown-menu {
        position: static !important;
        transform: none !important;
        box-shadow: none;
        border: none;
        padding-left: 1rem;
        background: transparent !important;
    }

    .dropdown-item {
        padding-left: 2rem !important;
    }
}

/* Dark Theme Support */
[data-bs-theme="dark"] .navbar-expand-lg.top {
    background: #1e293b;
    border-bottom-color: #334155;
}

[data-bs-theme="dark"] .nav-brand .nav-link {
    color: #f1f5f9;
}

[data-bs-theme="dark"] .navbar-nav > .nav-item > .nav-link {
    color: #e2e8f0;
}

[data-bs-theme="dark"] .navbar-nav > .nav-item > .nav-link:hover {
    background-color: #334155;
    color: #60a5fa;
}

[data-bs-theme="dark"] .dropdown-menu {
    background: #1e293b;
    border-color: #334155;
}

[data-bs-theme="dark"] .dropdown-item {
    color: #e2e8f0;
}

[data-bs-theme="dark"] .dropdown-item:hover {
    background-color: #334155;
    color: #60a5fa;
}
</style>
@endpush

@push('after_scripts')
<script>
// Horizontal Header Navigation Handler
(function() {
    'use strict';

    // Initialize dropdown toggles for horizontal navigation
    function initDropdowns() {
        const dropdownToggles = document.querySelectorAll('.nav-dropdown-toggle');
        
        dropdownToggles.forEach(toggle => {
            const parent = toggle.closest('.nav-dropdown');
            const dropdownItems = parent.querySelector('.nav-dropdown-items');
            
            if (dropdownItems) {
                // Convert nav-dropdown-items to Bootstrap dropdown-menu
                dropdownItems.classList.add('dropdown-menu');
                
                // Add data-bs-toggle to the toggle link
                toggle.setAttribute('data-bs-toggle', 'dropdown');
                toggle.setAttribute('data-bs-auto-close', 'outside');
                
                // Convert child nav-links to dropdown-items
                const childLinks = dropdownItems.querySelectorAll('.nav-link');
                childLinks.forEach(link => {
                    link.classList.remove('nav-link');
                    link.classList.add('dropdown-item');
                });
                
                // Handle nested dropdowns
                const nestedDropdowns = dropdownItems.querySelectorAll('.nav-dropdown');
                nestedDropdowns.forEach(nested => {
                    nested.classList.add('dropend');
                    const nestedToggle = nested.querySelector('.nav-dropdown-toggle');
                    const nestedItems = nested.querySelector('.nav-dropdown-items');
                    
                    if (nestedToggle && nestedItems) {
                        nestedToggle.classList.add('dropdown-toggle');
                        nestedToggle.setAttribute('data-bs-toggle', 'dropdown');
                        nestedToggle.setAttribute('data-bs-auto-close', 'outside');
                        nestedItems.classList.add('dropdown-menu', 'dropdown-submenu');
                    }
                });
            }
        });
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDropdowns);
    } else {
        initDropdowns();
    }
})();
</script>
@endpush
