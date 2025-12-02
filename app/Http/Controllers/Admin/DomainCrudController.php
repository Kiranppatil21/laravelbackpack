<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\DomainRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Models\Domain;
use App\Models\Tenant;

class DomainCrudController extends CrudController
{
    use CreateOperation;
    use DeleteOperation;
    use ListOperation;
    use ShowOperation;
    use UpdateOperation;

    public function setup(): void
    {
        CRUD::setModel(Domain::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/domain');
        CRUD::setEntityNameStrings('domain', 'domains');
    }

    protected function setupListOperation(): void
    {
        CRUD::column('id');
        CRUD::column('domain');
        CRUD::addColumn([
            'name' => 'tenant_id',
            'label' => 'Tenant ID',
        ]);
        CRUD::addColumn([
            'name' => 'created_at',
            'label' => 'Created',
        ]);
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation(DomainRequest::class);

        CRUD::field('domain');
        CRUD::addField([
            'name' => 'tenant_id',
            'label' => 'Tenant',
            'type' => 'select',
            'entity' => 'tenant',
            'attribute' => 'name',
            'model' => Tenant::class,
        ]);
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }
}
