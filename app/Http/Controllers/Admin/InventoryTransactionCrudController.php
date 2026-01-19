<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class InventoryTransactionCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\InventoryTransaction::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/inventory-transaction');
        CRUD::setEntityNameStrings('inventory transaction', 'inventory transactions');
    }

    protected function setupListOperation()
    {
        CRUD::column('transaction_type');
        CRUD::column('transaction_date');
        CRUD::column('quantity');
    }

    protected function setupCreateOperation()
    {
        CRUD::field('transaction_type')->type('select_from_array')->options([
            'purchase' => 'Purchase',
            'issue' => 'Issue',
            'return' => 'Return',
            'adjustment' => 'Adjustment'
        ]);
        CRUD::field('transaction_date')->type('date');
        CRUD::field('quantity')->type('number');
    }
}
