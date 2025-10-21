<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use App\Http\Requests\RoleRequest;
use Spatie\Permission\Models\Role;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class RoleCrudController extends CrudController
{
    use ListOperation;
    use CreateOperation;
    use UpdateOperation;
    use DeleteOperation;
    use ShowOperation;

    public function setup(): void
    {
        CRUD::setModel(Role::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/role');
        CRUD::setEntityNameStrings('role', 'roles');
    }

    protected function setupListOperation(): void
    {
        CRUD::column('id');
        CRUD::column('name');
        CRUD::column('guard_name');
        CRUD::addColumn([
            'name' => 'permissions', // relationship name on the Role model
            'type' => 'relationship',
            'label' => 'Permissions',
            'entity' => 'permissions',
            'attribute' => 'name',
            'model' => "Spatie\\Permission\\Models\\Permission",
        ]);
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation(RoleRequest::class);

        CRUD::field('name');
        CRUD::field('guard_name');
        // Use select2_from_ajax so admins can search existing permissions or create new ones inline.
        CRUD::addField([
            'label' => 'Permissions',
            'type' => 'select2_from_ajax_multiple',
            'name' => 'permissions', // relationship name on the Role model
            'entity' => 'permissions',
            'attribute' => 'name',
            'model' => "Spatie\\Permission\\Models\\Permission",
            'pivot' => true,
            'data_source' => url(config('backpack.base.route_prefix', 'admin').'/permission/ajax-search'),
            'placeholder' => 'Search or create permissions',
            'minimum_input_length' => 0,
            // allow custom tags — select2 will attempt to post names not in list; we'll create them on save
            'dependent' => null,
        ]);
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }

    public function store()
    {
        $response = $this->traitStore();

        $this->data['entry']->syncPermissions(request()->input('permissions', []));
        $this->syncPermissionsFromRequest($this->data['entry']);
        return $response;
    }

    public function update()
    {
        $response = $this->traitUpdate();

        $this->data['entry']->syncPermissions(request()->input('permissions', []));
        $this->syncPermissionsFromRequest($this->data['entry']);
        return $response;
    }
    
    /**
     * Helper to accept an array of permission ids or names, create missing permissions,
     * and sync them on the role.
     */
    protected function syncPermissionsFromRequest($role)
    {
        $input = request()->input('permissions', []);

        if (!is_array($input)) {
            $input = [$input];
        }

        $permissionIds = [];

        foreach ($input as $item) {
            if (is_numeric($item)) {
                $permissionIds[] = (int) $item;
                continue;
            }

            // treat as name; create if missing
            $perm = \Spatie\Permission\Models\Permission::firstOrCreate([
                'name' => (string) $item,
                'guard_name' => config('auth.defaults.guard'),
            ]);

            $permissionIds[] = $perm->id;
        }

        $role->syncPermissions($permissionIds);
    }
}
