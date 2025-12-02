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
        CRUD::setFromDb(); // set columns from db columns.

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

        // Add visible columns here
        $this->crud->addColumn(['name' => 'name', 'label' => 'Client Name']);
        $this->crud->addColumn(['name' => 'email', 'label' => 'Email']);
        $this->crud->addColumn([
            'name' => 'agency',
            'type' => 'relationship',
            'label' => 'Agency',
            'attribute' => 'name',
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
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    public function store()
    {
        $this->authorize('create', \App\Models\Client::class);

        // parent::store() is provided via Backpack operation traits at runtime
        // @phpstan-ignore-next-line
        return parent::store();
    }

    public function update()
    {
        $entry = $this->crud->getCurrentEntry();
        if ($entry) {
            $this->authorize('update', $entry);
        }

        // @phpstan-ignore-next-line
        return parent::update();
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
