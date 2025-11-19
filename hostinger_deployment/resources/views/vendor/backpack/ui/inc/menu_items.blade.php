{{-- This file is used for menu items by any Backpack v6 theme --}}

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

<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('employee') }}">
        <i class="nav-icon la la-users"></i>
        Employees
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('attendance') }}">
        <i class="nav-icon la la-clock"></i>
        Attendance
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('payroll') }}">
        <i class="nav-icon la la-money"></i>
        Payroll
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('invoice') }}">
        <i class="nav-icon la la-file-invoice"></i>
        Invoices
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

<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('employee') }}">
        <i class="nav-icon la la-users"></i>
        My Guards & Staff
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('attendance') }}">
        <i class="nav-icon la la-clock"></i>
        Staff Attendance
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('payroll') }}">
        <i class="nav-icon la la-money"></i>
        Staff Payroll
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('invoice') }}">
        <i class="nav-icon la la-file-invoice"></i>
        Client Invoices
    </a>
</li>
@endif

{{-- HR - People Management --}}
@if(backpack_user() && backpack_user()->hasRole('HR'))
<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('employee') }}">
        <i class="nav-icon la la-users"></i>
        Employees
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('attendance') }}">
        <i class="nav-icon la la-clock"></i>
        Attendance Records
    </a>
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

<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('attendance') }}">
        <i class="nav-icon la la-clock"></i>
        Guard Attendance
    </a>
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
