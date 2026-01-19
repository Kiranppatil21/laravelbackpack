<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\PermissionRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use App\Models\Permission;

class PermissionCrudController extends CrudController
{
    use CreateOperation;
    use DeleteOperation;
    use ListOperation;
    use ShowOperation;
    use UpdateOperation;

    public function setup(): void
    {
        CRUD::setModel(Permission::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/permissions'); // Changed from '/permission' to '/permissions'
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

        CRUD::addField([
            'name' => 'info_separator',
            'type' => 'custom_html',
            'value' => '<hr class="my-4"><h5 class="text-primary">🔐 Role-Based Permission Assignment</h5><p class="text-muted mb-3">Optionally assign this permission to a role and select applicable sections</p>',
        ]);

        CRUD::addField([
            'name' => 'temp_role_id',
            'label' => 'Assign to Role (Optional)',
            'type' => 'select2',
            'entity' => 'roles',
            'attribute' => 'name',
            'model' => 'Spatie\\Permission\\Models\\Role',
            'wrapper' => ['class' => 'form-group col-md-12'],
            'allows_null' => true,
            'hint' => 'Select a role to automatically assign this permission after creation',
        ]);

        CRUD::addField([
            'name' => 'temp_sections',
            'label' => 'Select Permission Sections (Optional)',
            'type' => 'checklist',
            'options' => [
                'user_management' => 'User Management',
                'employee_management' => 'Employee Management',
                'client_management' => 'Client Management',
                'agency_management' => 'Agency Management',
                'hr_management' => 'HR Management',
                'attendance_management' => 'Attendance Management',
                'payroll_management' => 'Payroll & Finance',
                'inventory_management' => 'Inventory Management',
                'operations_management' => 'Operations Management',
                'contract_management' => 'Contract Management',
                'incident_management' => 'Incident Management',
                'reports' => 'Reports & Analytics',
            ],
            'wrapper' => ['class' => 'form-group col-md-12'],
            'hint' => 'Tag this permission with relevant sections for better organization',
        ]);
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
