<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ClientRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class ClientCrudController
 *
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 * @method mixed store()
 * @method mixed update()
 * @method mixed destroy($id = null)
 */
class ClientCrudController extends CrudController
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\Client::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/client');
        CRUD::setEntityNameStrings('client', 'clients');
    }

    protected function setupListOperation()
    {
        // Don't use setFromDb() as it shows raw IDs for foreign keys
        // CRUD::setFromDb(); 

        // Super Admin can see all clients
        if (backpack_user() && backpack_user()->hasRole('Super Admin')) {
            // No filtering for Super Admin - show all clients
        }

        // If the current user is an agency_owner, only show their agency's records
        elseif (backpack_user() && backpack_user()->hasRole('agency_owner') && backpack_user()->tenant_id) {
            $this->crud->addClause('where', 'tenant_id', backpack_user()->tenant_id);
        }

        // For other users, show clients in their tenant (if they have one)
        elseif (backpack_user() && backpack_user()->tenant_id) {
            $this->crud->addClause('where', 'tenant_id', backpack_user()->tenant_id);
        }

        // Add visible columns with proper relationships
        $this->crud->addColumn(['name' => 'name', 'label' => 'Client Name', 'type' => 'text']);
        $this->crud->addColumn(['name' => 'email', 'label' => 'Email', 'type' => 'email']);
        $this->crud->addColumn(['name' => 'phone', 'label' => 'Phone', 'type' => 'text']);
        $this->crud->addColumn(['name' => 'address', 'label' => 'Address', 'type' => 'text']);
        $this->crud->addColumn([
            'name' => 'agency_id',
            'type' => 'select',
            'label' => 'Agency',
            'entity' => 'agency',
            'attribute' => 'name',
            'model' => "App\\Models\\Agency",
        ]);
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(ClientRequest::class);
        // Avoid `setFromDb()` here because it can infer PRO-only field types
        // (like `date_picker`) from the DB column types. Define fields
        // explicitly below to ensure only core Backpack field types are used.

        // Add form fields
        $this->crud->addField([
            'name' => 'name',
            'type' => 'text',
            'label' => 'Client Name',
        ]);

        $this->crud->addField([
            'name' => 'email',
            'type' => 'email',
            'label' => 'Email',
        ]);

        // Agency selector
        $this->crud->addField([
            'label' => 'Agency',
            'type' => 'select',
            'name' => 'agency_id',
            'entity' => 'agency',
            'attribute' => 'name',
            'model' => "App\\Models\\Agency",
            'wrapper' => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        $this->crud->addField([
            'name' => 'dob',
            'type' => 'date',
            'label' => 'Date of Birth',
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $this->crud->addField([
            'name' => 'date_of_anniversary',
            'type' => 'date',
            'label' => 'Anniversary Date',
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        // Financial / billing fields
        $this->crud->addField([
            'name' => 'billing_rate',
            'type' => 'number',
            'label' => 'Billing Rate',
            'attributes' => [
                'step' => '0.01',
                'min' => '0',
            ],
            'wrapper' => [ 'class' => 'form-group col-md-4' ],
        ]);

        $this->crud->addField([
            'name' => 'salary_cost',
            'type' => 'number',
            'label' => 'Salary Cost',
            'attributes' => [
                'step' => '0.01',
                'min' => '0',
            ],
            'wrapper' => [ 'class' => 'form-group col-md-4' ],
        ]);

        $this->crud->addField([
            'name' => 'esi_rate',
            'type' => 'number',
            'label' => 'ESI Rate (%)',
            'attributes' => [
                'step' => '0.01',
                'min' => '0',
                'max' => '100',
            ],
            'wrapper' => [ 'class' => 'form-group col-md-4' ],
        ]);

        $this->crud->addField([
            'name' => 'pf_rate',
            'type' => 'number',
            'label' => 'PF Rate (%)',
            'attributes' => [
                'step' => '0.01',
                'min' => '0',
                'max' => '100',
            ],
            'wrapper' => [ 'class' => 'form-group col-md-4' ],
        ]);

        $this->crud->addField([
            'name' => 'licensing_cost',
            'type' => 'number',
            'label' => 'Licensing Cost',
            'attributes' => [
                'step' => '0.01',
                'min' => '0',
            ],
            'wrapper' => [ 'class' => 'form-group col-md-4' ],
        ]);

        $this->crud->addField([
            'name' => 'administrative_overhead',
            'type' => 'number',
            'label' => 'Administrative Overhead',
            'attributes' => [
                'step' => '0.01',
                'min' => '0',
            ],
            'wrapper' => [ 'class' => 'form-group col-md-4' ],
        ]);

        $this->crud->addField([
            'name' => 'gross_margin',
            'type' => 'number',
            'label' => 'Gross Margin',
            'attributes' => [
                'step' => '0.01',
                'min' => '0',
            ],
            'hint' => 'Store gross margin as a monetary value. Consider computing percentage in reports.',
            'wrapper' => [ 'class' => 'form-group col-md-4' ],
        ]);

        // Role-wise salary section
        $this->crud->addField([
            'name' => 'role_salary_separator',
            'type' => 'custom_html',
            'value' => '<hr><h4 class="mt-4 mb-3 text-primary">📋 Role-wise Salary Configuration (for Invoice Calculation)</h4><p class="text-muted">Define salary rates for different roles at this client location. This is used for calculating client invoices.</p>',
        ]);

        $roles = ['Security Guard', 'Supervisor', 'Field Officer', 'Manager Staff', 'Watchman', 'Security Officer', 'Team Leader'];
        
        foreach ($roles as $role) {
            $fieldKey = strtolower(str_replace(' ', '_', $role));
            
            $this->crud->addField([
                'name' => 'role_salary_' . $fieldKey,
                'type' => 'number',
                'label' => $role . ' - Monthly Salary (₹)',
                'attributes' => [
                    'step' => '0.01',
                    'min' => '0',
                    'placeholder' => 'Enter salary for ' . $role,
                ],
                'wrapper' => [ 'class' => 'form-group col-md-6' ],
                'hint' => 'Monthly salary for ' . $role . ' position (for invoice)',
            ]);
        }

        // Employee salary configuration section
        $this->crud->addField([
            'name' => 'employee_salary_separator',
            'type' => 'custom_html',
            'value' => '<hr><h4 class="mt-4 mb-3 text-success">💰 Role-wise Salary Configuration for Employee (for Payroll Calculation)</h4><p class="text-muted">Define actual salary rates paid to employees for different roles. This is used for calculating employee payroll.</p>',
        ]);

        foreach ($roles as $role) {
            $fieldKey = strtolower(str_replace(' ', '_', $role));
            
            $this->crud->addField([
                'name' => 'employee_salary_' . $fieldKey,
                'type' => 'number',
                'label' => $role . ' - Employee Monthly Salary (₹)',
                'attributes' => [
                    'step' => '0.01',
                    'min' => '0',
                    'placeholder' => 'Enter employee salary for ' . $role,
                ],
                'wrapper' => [ 'class' => 'form-group col-md-6' ],
                'hint' => 'Monthly salary paid to ' . $role . ' employee (for payroll)',
            ]);
        }
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
        
        // Note: Designation rates management removed
        // This can be implemented separately if needed
    }

    public function store()
    {
        $this->authorize('create', \App\Models\Client::class);

        // parent::store() is provided via Backpack operation traits at runtime
        // @phpstan-ignore-next-line
        $response = parent::store();
        
        return $response;
    }

    public function update()
    {
        $entry = $this->crud->getCurrentEntry();
        if ($entry) {
            $this->authorize('update', $entry);
        }

        // @phpstan-ignore-next-line
        $response = parent::update();
        
        return $response;
    }

    public function destroy($id = null)
    {
        $entry = $this->crud->getCurrentEntry();
        if (! $entry && $id !== null) {
            $entry = $this->crud->getModel()::find($id);
        }

        if ($entry) {
            $this->authorize('delete', $entry);
        }

        // @phpstan-ignore-next-line
        return parent::destroy($id);
    }
}
