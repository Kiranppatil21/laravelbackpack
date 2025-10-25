<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ClientRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class ClientCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ClientCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Client::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/client');
        CRUD::setEntityNameStrings('client', 'clients');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::setFromDb(); // set columns from db columns.

        // If the current user is an agency_owner, only show their agency's records
        if (backpack_user()->hasRole('agency_owner') && backpack_user()->tenant_id) {
            $this->crud->addClause('where', 'tenant_id', backpack_user()->tenant_id);
        }

        // Add visible columns here (see below)
        $this->crud->addColumn(['name' => 'name', 'label' => 'Client Name']);
        $this->crud->addColumn(['name' => 'email', 'label' => 'Email']);
        // Add more columns as you need
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(ClientRequest::class);
        CRUD::setFromDb(); // set fields from db columns.

            // (Optional) If using FormRequest validation
        $this->crud->setValidation(\App\Http\Requests\ClientRequest::class);

        // Add form fields
        $this->crud->addField([
            'name' => 'name',
            'type' => 'text',
            'label' => 'Client Name'
        ]);
        $this->crud->addField([
            'name' => 'email',
            'type' => 'email',
            'label' => 'Email'
        ]);
        // Add additional fields as needed, for example:
        // $this->crud->addField([...]);
    }

    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
