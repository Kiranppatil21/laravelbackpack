<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\SupplierRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class SupplierCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\Supplier::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/supplier');
        CRUD::setEntityNameStrings('supplier', 'suppliers');
    }

    protected function setupListOperation()
    {
        CRUD::column('supplier_code');
        CRUD::column('company_name');
        CRUD::column('contact_person');
        CRUD::column('phone');
        CRUD::column('email');
        CRUD::column('status');
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(SupplierRequest::class);
        CRUD::field('supplier_code');
        CRUD::field('company_name');
        CRUD::field('contact_person');
        CRUD::field('phone');
        CRUD::field('email');
        CRUD::field('address')->type('textarea');
        CRUD::field('status')->type('select_from_array')->options(['active' => 'Active', 'inactive' => 'Inactive']);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
