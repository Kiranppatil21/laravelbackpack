<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\IncidentRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class IncidentCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\Incident::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/incident');
        CRUD::setEntityNameStrings('incident', 'incidents');
    }

    protected function setupListOperation()
    {
        CRUD::addColumn(['name' => 'incident_number', 'label' => 'Incident #', 'type' => 'text']);
        CRUD::addColumn(['name' => 'incident_type', 'label' => 'Type', 'type' => 'text']);
        CRUD::addColumn(['name' => 'severity', 'label' => 'Severity', 'type' => 'closure', 'function' => function($entry) {
            $colors = ['low' => 'info', 'medium' => 'warning', 'high' => 'danger', 'critical' => 'dark'];
            return '<span class="badge badge-' . ($colors[$entry->severity] ?? 'secondary') . '">' . ucfirst($entry->severity) . '</span>';
        }]);
        CRUD::addColumn(['name' => 'client_id', 'label' => 'Client', 'type' => 'select', 'entity' => 'client', 'attribute' => 'company_name', 'model' => "App\Models\Client"]);
        CRUD::addColumn(['name' => 'incident_datetime', 'label' => 'Date/Time', 'type' => 'datetime']);
        CRUD::addColumn(['name' => 'status', 'label' => 'Status', 'type' => 'text']);
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(IncidentRequest::class);
        CRUD::addField(['name' => 'incident_number', 'label' => 'Incident Number', 'type' => 'text']);
        CRUD::addField(['name' => 'incident_type', 'label' => 'Incident Type', 'type' => 'select_from_array', 'options' => ['theft' => 'Theft', 'assault' => 'Assault', 'fire' => 'Fire', 'medical' => 'Medical', 'accident' => 'Accident', 'property-damage' => 'Property Damage', 'suspicious-activity' => 'Suspicious Activity', 'breach' => 'Security Breach']]);
        CRUD::addField(['name' => 'severity', 'label' => 'Severity', 'type' => 'select_from_array', 'options' => ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical']]);
        CRUD::addField(['name' => 'client_id', 'label' => 'Client', 'type' => 'select', 'entity' => 'client', 'attribute' => 'company_name', 'model' => "App\Models\Client"]);
        CRUD::addField(['name' => 'reported_by_employee_id', 'label' => 'Reported By', 'type' => 'select', 'entity' => 'reportedBy', 'attribute' => 'name', 'model' => "App\Models\Employee"]);
        CRUD::addField(['name' => 'incident_datetime', 'label' => 'Incident Date/Time', 'type' => 'datetime']);
        CRUD::addField(['name' => 'location', 'label' => 'Location', 'type' => 'text']);
        CRUD::addField(['name' => 'description', 'label' => 'Description', 'type' => 'textarea']);
        CRUD::addField(['name' => 'action_taken', 'label' => 'Action Taken', 'type' => 'textarea']);
        CRUD::addField(['name' => 'police_notified', 'label' => 'Police Notified', 'type' => 'checkbox']);
        CRUD::addField(['name' => 'client_notified', 'label' => 'Client Notified', 'type' => 'checkbox']);
        CRUD::addField(['name' => 'status', 'label' => 'Status', 'type' => 'select_from_array', 'options' => ['open' => 'Open', 'investigating' => 'Investigating', 'resolved' => 'Resolved', 'closed' => 'Closed'], 'default' => 'open']);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
