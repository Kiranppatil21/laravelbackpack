<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\TrainingRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class TrainingCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\Training::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/training');
        CRUD::setEntityNameStrings('training', 'trainings');
    }

    protected function setupListOperation()
    {
        CRUD::addColumn(['name' => 'training_name', 'label' => 'Training Name', 'type' => 'text']);
        CRUD::addColumn(['name' => 'training_code', 'label' => 'Code', 'type' => 'text']);
        CRUD::addColumn(['name' => 'category', 'label' => 'Category', 'type' => 'text']);
        CRUD::addColumn(['name' => 'start_date', 'label' => 'Start Date', 'type' => 'date']);
        CRUD::addColumn(['name' => 'duration_hours', 'label' => 'Duration (hrs)', 'type' => 'number']);
        CRUD::addColumn(['name' => 'status', 'label' => 'Status', 'type' => 'text']);
        CRUD::addColumn(['name' => 'is_mandatory', 'label' => 'Mandatory', 'type' => 'boolean']);
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(TrainingRequest::class);
        CRUD::addField(['name' => 'training_name', 'label' => 'Training Name', 'type' => 'text']);
        CRUD::addField(['name' => 'training_code', 'label' => 'Training Code', 'type' => 'text']);
        CRUD::addField(['name' => 'category', 'label' => 'Category', 'type' => 'select_from_array', 'options' => ['security' => 'Security', 'safety' => 'Safety', 'first-aid' => 'First Aid', 'fire-fighting' => 'Fire Fighting', 'customer-service' => 'Customer Service', 'technical' => 'Technical']]);
        CRUD::addField(['name' => 'description', 'label' => 'Description', 'type' => 'textarea']);
        CRUD::addField(['name' => 'trainer_name', 'label' => 'Trainer Name', 'type' => 'text']);
        CRUD::addField(['name' => 'trainer_contact', 'label' => 'Trainer Contact', 'type' => 'text']);
        CRUD::addField(['name' => 'start_date', 'label' => 'Start Date', 'type' => 'date']);
        CRUD::addField(['name' => 'end_date', 'label' => 'End Date', 'type' => 'date']);
        CRUD::addField(['name' => 'duration_hours', 'label' => 'Duration (Hours)', 'type' => 'number']);
        CRUD::addField(['name' => 'venue', 'label' => 'Venue', 'type' => 'text']);
        CRUD::addField(['name' => 'max_participants', 'label' => 'Max Participants', 'type' => 'number']);
        CRUD::addField(['name' => 'is_mandatory', 'label' => 'Mandatory Training', 'type' => 'checkbox']);
        CRUD::addField(['name' => 'status', 'label' => 'Status', 'type' => 'select_from_array', 'options' => ['scheduled' => 'Scheduled', 'ongoing' => 'Ongoing', 'completed' => 'Completed', 'cancelled' => 'Cancelled'], 'default' => 'scheduled']);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
