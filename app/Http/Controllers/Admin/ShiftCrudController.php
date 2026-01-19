<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ShiftRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class ShiftCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\Shift::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/shift');
        CRUD::setEntityNameStrings('shift', 'shifts');
    }

    protected function setupListOperation()
    {
        CRUD::addColumn(['name' => 'shift_name', 'label' => 'Shift Name', 'type' => 'text']);
        CRUD::addColumn(['name' => 'shift_code', 'label' => 'Code', 'type' => 'text']);
        CRUD::addColumn(['name' => 'start_time', 'label' => 'Start Time', 'type' => 'time']);
        CRUD::addColumn(['name' => 'end_time', 'label' => 'End Time', 'type' => 'time']);
        CRUD::addColumn(['name' => 'duration_hours', 'label' => 'Duration (hrs)', 'type' => 'number']);
        CRUD::addColumn(['name' => 'is_night_shift', 'label' => 'Night Shift', 'type' => 'boolean']);
        CRUD::addColumn(['name' => 'is_active', 'label' => 'Active', 'type' => 'boolean']);
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(ShiftRequest::class);
        CRUD::addField(['name' => 'shift_name', 'label' => 'Shift Name', 'type' => 'text']);
        CRUD::addField(['name' => 'shift_code', 'label' => 'Shift Code', 'type' => 'text']);
        CRUD::addField(['name' => 'start_time', 'label' => 'Start Time', 'type' => 'time']);
        CRUD::addField(['name' => 'end_time', 'label' => 'End Time', 'type' => 'time']);
        CRUD::addField(['name' => 'duration_hours', 'label' => 'Duration (Hours)', 'type' => 'number']);
        CRUD::addField(['name' => 'ot_after_hours', 'label' => 'OT After (Hours)', 'type' => 'number', 'attributes' => ['step' => '0.5']]);
        CRUD::addField(['name' => 'is_night_shift', 'label' => 'Night Shift', 'type' => 'checkbox']);
        CRUD::addField(['name' => 'night_allowance', 'label' => 'Night Allowance', 'type' => 'number', 'attributes' => ['step' => '0.01']]);
        CRUD::addField(['name' => 'description', 'label' => 'Description', 'type' => 'textarea']);
        CRUD::addField(['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'default' => true]);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
