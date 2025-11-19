@php
    $user = backpack_user();
@endphp

<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
    {{-- Existing menu items... --}}

    @if (backpack_auth()->check())
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('user') }}">
                <i class="nav-icon la la-user"></i>
                <p>Users</p>
            </a>
        </li>

    {{-- Show roles & permissions only to users in configured allowed roles --}}
    @php $allowed = config('backpack-permissions.sidebar_allowed_roles', ['Super Admin']); @endphp
    @if (method_exists(backpack_user(), 'hasAnyRole') && backpack_user()->hasAnyRole($allowed))
            <li class="nav-item">
                <a class="nav-link" href="{{ backpack_url('tenant') }}">
                    <i class="nav-icon la la-building"></i>
                    <p>Tenants</p>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ backpack_url('role') }}">
                    <i class="nav-icon la la-user-shield"></i>
                    <p>Roles</p>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ backpack_url('permission') }}">
                    <i class="nav-icon la la-key"></i>
                    <p>Permissions</p>
                </a>
            </li>
            
            {{-- Bulk Attendance Management --}}
            <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                    <i class="nav-icon la la-calendar-check-o"></i>
                    <p>
                        Bulk Attendance
                        <i class="right la la-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="{{ route('admin.bulk-attendance.index') }}" class="nav-link">
                            <i class="la la-plus nav-icon"></i>
                            <p>Create Attendance</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.bulk-attendance.view') }}" class="nav-link">
                            <i class="la la-list nav-icon"></i>
                            <p>View Records</p>
                        </a>
                    </li>
                </ul>
            </li>
        @endif
    @endif

    {{-- Keep adding items as needed --}}
</ul>

{{-- Include global AJAX CSRF fix for all admin pages --}}
@include('admin.global_ajax_csrf_fix')
