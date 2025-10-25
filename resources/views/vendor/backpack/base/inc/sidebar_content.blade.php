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
        @endif
    @endif

    {{-- Keep adding items as needed --}}
</ul>
