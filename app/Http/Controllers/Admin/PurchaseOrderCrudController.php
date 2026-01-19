<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class PurchaseOrderCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\PurchaseOrder::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/purchase-order');
        CRUD::setEntityNameStrings('purchase order', 'purchase orders');
    }

    protected function setupListOperation()
    {
        CRUD::column('po_number');
        CRUD::column('order_date');
        CRUD::column('status');
        CRUD::column('total_amount');
    }

    protected function setupCreateOperation()
    {
        CRUD::field('po_number');
        CRUD::field('order_date')->type('date');
        CRUD::field('status')->type('select_from_array')->options([
            'pending' => 'Pending',
            'approved' => 'Approved',
            'ordered' => 'Ordered',
            'received' => 'Received'
        ]);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
