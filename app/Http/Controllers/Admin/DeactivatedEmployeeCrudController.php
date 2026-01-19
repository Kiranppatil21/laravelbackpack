<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\EmployeeRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class DeactivatedEmployeeCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\Employee::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/deactivated-employee');
        CRUD::setEntityNameStrings('deactivated employee', 'deactivated employees');
    }

    protected function setupListOperation()
    {
        // Bypass tenant scope for testing
        $this->crud->addClause('withoutGlobalScope', \App\Models\Scopes\TenantScope::class);
        
        // Add clause to show only inactive employees
        $this->crud->addClause('where', 'status', 'inactive');
        
        CRUD::addColumn([
            'name' => 'name',
            'label' => 'Full Name',
            'type' => 'text',
        ]);

        CRUD::addColumn([
            'name' => 'email',
            'label' => 'Email',
            'type' => 'email',
        ]);

        CRUD::addColumn([
            'name' => 'phone',
            'label' => 'Phone',
            'type' => 'phone',
        ]);

        CRUD::addColumn([
            'name' => 'client_id',
            'label' => 'Assigned Client', 
            'type' => 'closure',
            'function' => function($entry) {
                if ($entry->client_id) {
                    $client = \App\Models\Client::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)->find($entry->client_id);
                    if ($client) {
                        return '<span class="badge badge-secondary">' . $client->name . '</span>';
                    }
                }
                return '<span class="badge badge-secondary">Not Assigned</span>';
            },
            'escaped' => false,
        ]);

        CRUD::addColumn([
            'name' => 'status',
            'label' => 'Status',
            'type' => 'closure',
            'function' => function($entry) {
                return '<span class="badge badge-danger">Inactive</span>';
            },
            'escaped' => false,
        ]);

        CRUD::addColumn([
            'name' => 'job_role',
            'label' => 'Role',
            'type' => 'text',
        ]);
    }

    protected function setupShowOperation()
    {
        $this->setupListOperation();
    }
}
