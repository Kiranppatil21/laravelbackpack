@extends(backpack_view('blank'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="la la-users-cog"></i> Manage Access for: <strong>{{ $menu->menu_label }}</strong>
                    </h3>
                    <div class="card-tools">
                        <a href="{{ backpack_url('menu-permission') }}" class="btn btn-sm btn-secondary">
                            <i class="la la-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="la la-info-circle"></i> 
                        <strong>Menu Key:</strong> {{ $menu->menu_key }} | 
                        <strong>URL:</strong> {{ $menu->menu_url ?? 'N/A' }}
                        @if($menu->parent_key)
                            | <strong>Parent:</strong> {{ $menu->parent_key }}
                        @endif
                    </div>

                    <form method="POST" action="{{ url(config('backpack.base.route_prefix').'/menu-permission/'.$menu->id.'/save-access') }}">
                        @csrf
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th width="50">#</th>
                                        <th>Role Name</th>
                                        <th width="150" class="text-center">Can Access</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($roles as $index => $role)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $role->name }}</strong>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check form-switch d-inline-block">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       name="role_access[{{ $role->id }}]" 
                                                       id="role_{{ $role->id }}"
                                                       value="1"
                                                       {{ isset($roleAccess[$role->id]) && $roleAccess[$role->id] ? 'checked' : '' }}>
                                                <label class="form-check-label" for="role_{{ $role->id }}">
                                                    {{ isset($roleAccess[$role->id]) && $roleAccess[$role->id] ? 'Yes' : 'No' }}
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">
                                            <i class="la la-info-circle"></i> No roles found
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="la la-save"></i> Save Permissions
                            </button>
                            <a href="{{ backpack_url('menu-permission') }}" class="btn btn-secondary">
                                <i class="la la-ban"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('after_scripts')
<script>
$(document).ready(function() {
    // Update label text when checkbox changes
    $('.form-check-input').on('change', function() {
        const label = $(this).next('label');
        if ($(this).is(':checked')) {
            label.text('Yes');
        } else {
            label.text('No');
        }
    });
});
</script>
@endpush
@endsection
