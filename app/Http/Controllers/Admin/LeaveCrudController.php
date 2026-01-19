<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\LeaveRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class LeaveCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\Leave::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/leave');
        CRUD::setEntityNameStrings('leave', 'leaves');
    }

    protected function setupListOperation()
    {
        CRUD::addColumn(['name' => 'employee_id', 'label' => 'Employee', 'type' => 'select', 'entity' => 'employee', 'attribute' => 'name', 'model' => "App\Models\Employee"]);
        CRUD::addColumn(['name' => 'leave_type', 'label' => 'Leave Type', 'type' => 'text']);
        CRUD::addColumn(['name' => 'start_date', 'label' => 'Start Date', 'type' => 'date']);
        CRUD::addColumn(['name' => 'end_date', 'label' => 'End Date', 'type' => 'date']);
        CRUD::addColumn(['name' => 'days', 'label' => 'Days', 'type' => 'number', 'decimals' => 1]);
        CRUD::addColumn(['name' => 'status', 'label' => 'Status', 'type' => 'closure', 'function' => function($entry) {
            $colors = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', 'cancelled' => 'secondary'];
            $color = $colors[$entry->status] ?? 'info';
            return '<span class="badge badge-' . $color . '">' . ucfirst($entry->status) . '</span>';
        }]);
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(LeaveRequest::class);
        CRUD::addField(['name' => 'employee_id', 'label' => 'Employee', 'type' => 'select', 'entity' => 'employee', 'attribute' => 'name', 'model' => "App\Models\Employee"]);
        CRUD::addField(['name' => 'leave_type', 'label' => 'Leave Type', 'type' => 'select_from_array', 'options' => ['casual' => 'Casual', 'sick' => 'Sick', 'annual' => 'Annual', 'compensatory' => 'Compensatory', 'maternity' => 'Maternity', 'paternity' => 'Paternity', 'unpaid' => 'Unpaid', 'public_holiday' => 'Public Holiday']]);
        CRUD::addField(['name' => 'start_date', 'label' => 'Start Date', 'type' => 'date']);
        CRUD::addField(['name' => 'end_date', 'label' => 'End Date', 'type' => 'date']);
        CRUD::addField(['name' => 'days', 'label' => 'Days', 'type' => 'number', 'attributes' => ['step' => '0.5']]);
        CRUD::addField(['name' => 'reason', 'label' => 'Reason', 'type' => 'textarea']);
        CRUD::addField(['name' => 'is_half_day', 'label' => 'Half Day', 'type' => 'checkbox']);
        CRUD::addField(['name' => 'status', 'label' => 'Status', 'type' => 'select_from_array', 'options' => ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'], 'default' => 'pending']);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
