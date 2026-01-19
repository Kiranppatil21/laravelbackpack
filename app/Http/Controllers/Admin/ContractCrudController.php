<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ContractRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class ContractCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\Contract::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/contract');
        CRUD::setEntityNameStrings('contract', 'contracts');
    }

    protected function setupListOperation()
    {
        CRUD::addColumn(['name' => 'contract_number', 'label' => 'Contract #', 'type' => 'text']);
        CRUD::addColumn(['name' => 'client_id', 'label' => 'Client', 'type' => 'select', 'entity' => 'client', 'attribute' => 'company_name', 'model' => "App\Models\Client"]);
        CRUD::addColumn(['name' => 'contract_type', 'label' => 'Type', 'type' => 'text']);
        CRUD::addColumn(['name' => 'start_date', 'label' => 'Start Date', 'type' => 'date']);
        CRUD::addColumn(['name' => 'end_date', 'label' => 'End Date', 'type' => 'date']);
        CRUD::addColumn(['name' => 'monthly_contract_value', 'label' => 'Monthly Value', 'type' => 'number', 'prefix' => '₹', 'decimals' => 2]);
        CRUD::addColumn(['name' => 'status', 'label' => 'Status', 'type' => 'closure', 'function' => function($entry) {
            $colors = ['draft' => 'secondary', 'active' => 'success', 'expired' => 'warning', 'cancelled' => 'danger'];
            return '<span class="badge badge-' . ($colors[$entry->status] ?? 'info') . '">' . ucfirst($entry->status) . '</span>';
        }]);
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(ContractRequest::class);
        CRUD::addField(['name' => 'contract_number', 'label' => 'Contract Number', 'type' => 'text']);
        CRUD::addField(['name' => 'client_id', 'label' => 'Client', 'type' => 'select', 'entity' => 'client', 'attribute' => 'company_name', 'model' => "App\Models\Client"]);
        CRUD::addField(['name' => 'agency_id', 'label' => 'Agency', 'type' => 'select', 'entity' => 'agency', 'attribute' => 'name', 'model' => "App\Models\Agency"]);
        CRUD::addField(['name' => 'contract_type', 'label' => 'Contract Type', 'type' => 'select_from_array', 'options' => ['security-services' => 'Security Services', 'manpower' => 'Manpower', 'facility-management' => 'Facility Management', 'event-security' => 'Event Security']]);
        CRUD::addField(['name' => 'service_type', 'label' => 'Service Type', 'type' => 'text']);
        CRUD::addField(['name' => 'start_date', 'label' => 'Start Date', 'type' => 'date']);
        CRUD::addField(['name' => 'end_date', 'label' => 'End Date', 'type' => 'date']);
        CRUD::addField(['name' => 'duration_months', 'label' => 'Duration (Months)', 'type' => 'number']);
        CRUD::addField(['name' => 'number_of_guards', 'label' => 'Number of Guards', 'type' => 'number']);
        CRUD::addField(['name' => 'shift_pattern', 'label' => 'Shift Pattern', 'type' => 'select_from_array', 'options' => ['12-hour' => '12 Hour', '8-hour' => '8 Hour', '24-hour' => '24 Hour']]);
        CRUD::addField(['name' => 'monthly_contract_value', 'label' => 'Monthly Contract Value', 'type' => 'number', 'attributes' => ['step' => '0.01']]);
        CRUD::addField(['name' => 'per_guard_rate', 'label' => 'Per Guard Rate', 'type' => 'number', 'attributes' => ['step' => '0.01']]);
        CRUD::addField(['name' => 'billing_cycle', 'label' => 'Billing Cycle', 'type' => 'select_from_array', 'options' => ['monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'annual' => 'Annual']]);
        CRUD::addField(['name' => 'scope_of_work', 'label' => 'Scope of Work', 'type' => 'textarea']);
        CRUD::addField(['name' => 'status', 'label' => 'Status', 'type' => 'select_from_array', 'options' => ['draft' => 'Draft', 'active' => 'Active', 'expired' => 'Expired', 'cancelled' => 'Cancelled'], 'default' => 'draft']);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
