<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\PayrollRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class PayslipCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\Payslip::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/payslip');
        CRUD::setEntityNameStrings('payslip', 'payslips');
    }

    protected function setupListOperation()
    {
        CRUD::column('id')->label('ID');
        CRUD::column('employee_id')->label('Employee ID');
        CRUD::column('period_start')->label('Period Start')->type('date');
        CRUD::column('period_end')->label('Period End')->type('date');
        CRUD::column('gross')->label('Gross');
        CRUD::column('net')->label('Net');
        CRUD::column('created_at')->label('Created At')->type('datetime');
    }

    protected function setupCreateOperation()
    {
        CRUD::field('employee_id')->label('Employee ID')->type('number');
        CRUD::field('tenant_uuid')->label('Tenant UUID')->type('text');
        CRUD::field('period_start')->label('Period Start')->type('date');
        CRUD::field('period_end')->label('Period End')->type('date');
        CRUD::field('gross')->label('Gross')->type('number')->attributes(['step' => '0.01']);
        CRUD::field('net')->label('Net')->type('number')->attributes(['step' => '0.01']);
        CRUD::field('breakdown')->label('Breakdown')->type('textarea');
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}