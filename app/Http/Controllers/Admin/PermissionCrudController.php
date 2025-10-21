<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use App\Http\Requests\PermissionRequest;
use Spatie\Permission\Models\Permission;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Http\JsonResponse;

class PermissionCrudController extends CrudController
{
    use ListOperation;
    use CreateOperation;
    use UpdateOperation;
    use DeleteOperation;
    use ShowOperation;
    public function setup(): void
    {
        CRUD::setModel(Permission::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/permission');
        CRUD::setEntityNameStrings('permission', 'permissions');
    }

    protected function setupListOperation(): void
    {
        CRUD::column('id');
        CRUD::column('name');
        CRUD::column('guard_name');
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation(PermissionRequest::class);

        CRUD::field('name');
        CRUD::field('guard_name');
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }

    /**
     * AJAX endpoint to create a permission by name if it does not already exist.
     * Expected payload: { name: 'permission name', guard_name: 'web' }
     */
    public function ajaxCreate(HttpRequest $request): JsonResponse
    {
        $this->crud->hasAccessOrFail('create');

        $name = (string) $request->input('name');
        $guard = $request->input('guard_name', config('auth.defaults.guard'));

        if (empty($name)) {
            return response()->json(['error' => 'Name is required.'], 422);
        }

        $permission = Permission::firstOrCreate([
            'name' => $name,
            'guard_name' => $guard,
        ]);

        return response()->json(['id' => $permission->id, 'text' => $permission->name]);
    }

    /**
     * AJAX search endpoint for select2. Returns id/text pairs filtered by q param.
     */
    public function ajaxSearch(HttpRequest $request): JsonResponse
    {
        $this->crud->hasAccessOrFail('list');

        $q = $request->input('q', '');

        $results = Permission::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%");
            })
            ->limit(20)
            ->get()
            ->map(function ($p) {
                return ['id' => $p->id, 'text' => $p->name];
            });

        return response()->json(['results' => $results]);
    }
}
