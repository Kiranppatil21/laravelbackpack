<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\MenuPermissionRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Models\MenuPermission;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class MenuPermissionCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\MenuPermission::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/menu-permission');
        CRUD::setEntityNameStrings('menu permission', 'menu permissions');
    }

    protected function setupListOperation()
    {
        CRUD::addColumn([
            'name' => 'menu_label',
            'label' => 'Menu Item',
            'type' => 'text',
        ]);

        CRUD::addColumn([
            'name' => 'menu_key',
            'label' => 'Menu Key',
            'type' => 'text',
        ]);

        CRUD::addColumn([
            'name' => 'menu_url',
            'label' => 'URL',
            'type' => 'text',
        ]);

        CRUD::addColumn([
            'name' => 'parent_key',
            'label' => 'Parent Menu',
            'type' => 'closure',
            'function' => function($entry) {
                if ($entry->parent_key) {
                    $parent = MenuPermission::where('menu_key', $entry->parent_key)->first();
                    return $parent ? $parent->menu_label : $entry->parent_key;
                }
                return '<span class="badge badge-primary">Top Level</span>';
            },
            'escaped' => false,
        ]);

        CRUD::addColumn([
            'name' => 'sort_order',
            'label' => 'Order',
            'type' => 'number',
        ]);

        CRUD::addColumn([
            'name' => 'is_active',
            'label' => 'Status',
            'type' => 'boolean',
            'options' => [
                0 => 'Inactive',
                1 => 'Active'
            ],
        ]);

        // Add button to manage role access
        CRUD::addButtonFromView('line', 'manage_access', 'admin.buttons.manage_menu_access', 'beginning');
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(MenuPermissionRequest::class);

        CRUD::addField([
            'name' => 'info_header',
            'type' => 'custom_html',
            'value' => '<div class="alert alert-info"><strong>🔒 Role-Based Access Control</strong><br>Select which roles can access this menu item. If no roles are selected, the menu will be visible to all authenticated users.</div>',
        ]);

        CRUD::addField([
            'name' => 'temp_roles_access',
            'label' => 'Assign Access to Roles',
            'type' => 'select2_multiple',
            'entity' => 'roles',
            'attribute' => 'name',
            'model' => 'Spatie\\Permission\\Models\\Role',
            'wrapper' => ['class' => 'form-group col-md-12'],
            'hint' => 'Select which roles can view and access this menu item',
            'allows_null' => true,
        ]);

        CRUD::addField([
            'name' => 'separator_1',
            'type' => 'custom_html',
            'value' => '<hr class="my-4"><h5 class="text-primary">📋 Menu Item Details</h5>',
        ]);

        CRUD::addField([
            'name' => 'menu_label',
            'label' => 'Menu Label',
            'type' => 'text',
            'attributes' => [
                'required' => 'required',
            ],
        ]);

        CRUD::addField([
            'name' => 'menu_key',
            'label' => 'Menu Key',
            'type' => 'text',
            'hint' => 'Unique identifier (e.g., employee_management, hr_leave)',
            'attributes' => [
                'required' => 'required',
            ],
        ]);

        CRUD::addField([
            'name' => 'menu_url',
            'label' => 'Menu URL',
            'type' => 'text',
            'hint' => 'Route or URL (optional for parent menus)',
        ]);

        CRUD::addField([
            'name' => 'parent_key',
            'label' => 'Parent Menu',
            'type' => 'select_from_array',
            'options' => MenuPermission::whereNull('parent_key')
                ->pluck('menu_label', 'menu_key')
                ->toArray(),
            'allows_null' => true,
            'hint' => 'Leave empty for top-level menu items',
        ]);

        CRUD::addField([
            'name' => 'sort_order',
            'label' => 'Sort Order',
            'type' => 'number',
            'default' => 0,
        ]);

        CRUD::addField([
            'name' => 'is_active',
            'label' => 'Active',
            'type' => 'checkbox',
            'default' => true,
        ]);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    /**
     * Manage role access to menu items
     */
    public function manageAccess($menuId)
    {
        $menu = MenuPermission::findOrFail($menuId);
        $roles = Role::all();
        
        // Get current access permissions
        $roleAccess = [];
        foreach ($roles as $role) {
            $access = \App\Models\RoleMenuPermission::where('role_id', $role->id)
                ->where('menu_permission_id', $menuId)
                ->first();
            
            $roleAccess[$role->id] = $access ? $access->can_access : false;
        }
        
        return view('admin.menu_permissions.manage_access', compact('menu', 'roles', 'roleAccess'));
    }

    /**
     * Save role access permissions
     */
    public function saveAccess(Request $request, $menuId)
    {
        $menu = MenuPermission::findOrFail($menuId);
        $roleAccess = $request->input('role_access', []);
        
        foreach ($roleAccess as $roleId => $canAccess) {
            \App\Models\RoleMenuPermission::updateOrCreate(
                [
                    'role_id' => $roleId,
                    'menu_permission_id' => $menuId,
                ],
                [
                    'can_access' => (bool) $canAccess,
                ]
            );
        }
        
        \Alert::success('Menu access permissions updated successfully')->flash();
        
        return redirect()->back();
    }

    /**
     * Initialize default menu items
     */
    public function seedMenus()
    {
        $menus = [
            // Top level menus
            ['menu_key' => 'dashboard', 'menu_label' => 'Dashboard', 'menu_url' => 'admin/dashboard', 'parent_key' => null, 'sort_order' => 1],
            ['menu_key' => 'user_management', 'menu_label' => 'User Management', 'menu_url' => null, 'parent_key' => null, 'sort_order' => 2],
            ['menu_key' => 'employee_management', 'menu_label' => 'Employee Management', 'menu_url' => null, 'parent_key' => null, 'sort_order' => 3],
            ['menu_key' => 'hr_management', 'menu_label' => 'HR Management', 'menu_url' => null, 'parent_key' => null, 'sort_order' => 4],
            ['menu_key' => 'operations_management', 'menu_label' => 'Operations Management', 'menu_url' => null, 'parent_key' => null, 'sort_order' => 5],
            ['menu_key' => 'inventory_management', 'menu_label' => 'Inventory Management', 'menu_url' => null, 'parent_key' => null, 'sort_order' => 6],
            
            // User Management submenu
            ['menu_key' => 'users', 'menu_label' => 'Users', 'menu_url' => 'admin/user', 'parent_key' => 'user_management', 'sort_order' => 1],
            ['menu_key' => 'roles', 'menu_label' => 'Roles', 'menu_url' => 'admin/roles', 'parent_key' => 'user_management', 'sort_order' => 2],
            ['menu_key' => 'permissions', 'menu_label' => 'Permissions', 'menu_url' => 'admin/permissions', 'parent_key' => 'user_management', 'sort_order' => 3],
            
            // Employee Management submenu
            ['menu_key' => 'employees', 'menu_label' => 'All Employees', 'menu_url' => 'admin/employee', 'parent_key' => 'employee_management', 'sort_order' => 1],
            ['menu_key' => 'deactivated_employees', 'menu_label' => 'Deactivated Employees', 'menu_url' => 'admin/deactivated-employee', 'parent_key' => 'employee_management', 'sort_order' => 2],
            
            // HR Management submenu
            ['menu_key' => 'leave_management', 'menu_label' => 'Leave Management', 'menu_url' => 'admin/leave', 'parent_key' => 'hr_management', 'sort_order' => 1],
            ['menu_key' => 'leave_reports', 'menu_label' => 'Leave Reports', 'menu_url' => 'admin/reports/leave', 'parent_key' => 'hr_management', 'sort_order' => 2],
            ['menu_key' => 'shift_management', 'menu_label' => 'Shift Management', 'menu_url' => 'admin/shift', 'parent_key' => 'hr_management', 'sort_order' => 3],
            ['menu_key' => 'shift_reports', 'menu_label' => 'Shift Reports', 'menu_url' => 'admin/reports/shift', 'parent_key' => 'hr_management', 'sort_order' => 4],
            ['menu_key' => 'training_management', 'menu_label' => 'Training Programs', 'menu_url' => 'admin/training', 'parent_key' => 'hr_management', 'sort_order' => 5],
            ['menu_key' => 'training_reports', 'menu_label' => 'Training Reports', 'menu_url' => 'admin/reports/training', 'parent_key' => 'hr_management', 'sort_order' => 6],
            
            // Operations Management submenu
            ['menu_key' => 'incidents', 'menu_label' => 'Incident Reports', 'menu_url' => 'admin/incident', 'parent_key' => 'operations_management', 'sort_order' => 1],
            ['menu_key' => 'incident_reports', 'menu_label' => 'Incident Analysis', 'menu_url' => 'admin/reports/incident', 'parent_key' => 'operations_management', 'sort_order' => 2],
            ['menu_key' => 'contracts', 'menu_label' => 'Contracts', 'menu_url' => 'admin/contract', 'parent_key' => 'operations_management', 'sort_order' => 3],
            ['menu_key' => 'contract_reports', 'menu_label' => 'Contract Reports', 'menu_url' => 'admin/reports/contract', 'parent_key' => 'operations_management', 'sort_order' => 4],
        ];

        foreach ($menus as $menu) {
            MenuPermission::updateOrCreate(
                ['menu_key' => $menu['menu_key']],
                $menu
            );
        }

        \Alert::success('Default menu items created successfully')->flash();
        
        return redirect()->back();
    }
}
