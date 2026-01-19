<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AssetRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class AssetCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class AssetCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Asset::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/asset');
        CRUD::setEntityNameStrings('asset', 'assets');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::addColumn([
            'name' => 'asset_code',
            'label' => 'Asset Code',
            'type' => 'text',
        ]);
        
        CRUD::addColumn([
            'name' => 'asset_name',
            'label' => 'Asset Name',
            'type' => 'text',
        ]);

        CRUD::addColumn([
            'name' => 'category',
            'label' => 'Category',
            'type' => 'text',
        ]);

        CRUD::addColumn([
            'name' => 'brand',
            'label' => 'Brand',
            'type' => 'text',
        ]);

        CRUD::addColumn([
            'name' => 'status',
            'label' => 'Status',
            'type' => 'closure',
            'function' => function($entry) {
                return $entry->status_badge;
            },
        ]);

        CRUD::addColumn([
            'name' => 'condition',
            'label' => 'Condition',
            'type' => 'text',
        ]);

        CRUD::addColumn([
            'name' => 'assigned_to_employee_id',
            'label' => 'Assigned To',
            'type' => 'select',
            'entity' => 'assignedEmployee',
            'attribute' => 'name',
            'model' => "App\\Models\\Employee",
        ]);

        CRUD::addColumn([
            'name' => 'purchase_price',
            'label' => 'Purchase Price',
            'type' => 'number',
            'prefix' => '₹ ',
            'decimals' => 2,
        ]);

        CRUD::addColumn([
            'name' => 'location',
            'label' => 'Location',
            'type' => 'text',
        ]);
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(AssetRequest::class);

        // Basic Information Section
        CRUD::addField([
            'name' => 'basic_info_separator',
            'type' => 'custom_html',
            'value' => '<h4 class="mt-4 mb-3 text-primary">📦 Basic Information</h4><hr>',
        ]);

        CRUD::addField([
            'name' => 'asset_name',
            'label' => 'Asset Name',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'asset_code',
            'label' => 'Asset Code',
            'type' => 'text',
            'hint' => 'Unique identifier for the asset (e.g., LAP-001, VEH-002)',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'category',
            'label' => 'Category',
            'type' => 'select_from_array',
            'options' => \App\Models\Asset::getCategories(),
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'status',
            'label' => 'Status',
            'type' => 'select_from_array',
            'options' => \App\Models\Asset::getStatuses(),
            'default' => 'available',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'description',
            'label' => 'Description',
            'type' => 'textarea',
            'attributes' => ['rows' => 3],
            'wrapper' => ['class' => 'form-group col-md-12'],
        ]);

        // Product Details Section
        CRUD::addField([
            'name' => 'product_details_separator',
            'type' => 'custom_html',
            'value' => '<h4 class="mt-4 mb-3 text-primary">🏷️ Product Details</h4><hr>',
        ]);

        CRUD::addField([
            'name' => 'brand',
            'label' => 'Brand',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-4'],
        ]);

        CRUD::addField([
            'name' => 'model',
            'label' => 'Model',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-4'],
        ]);

        CRUD::addField([
            'name' => 'serial_number',
            'label' => 'Serial Number',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-4'],
        ]);

        CRUD::addField([
            'name' => 'condition',
            'label' => 'Condition',
            'type' => 'select_from_array',
            'options' => \App\Models\Asset::getConditions(),
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'location',
            'label' => 'Location',
            'type' => 'text',
            'hint' => 'Physical location of the asset',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        // Purchase Information Section
        CRUD::addField([
            'name' => 'purchase_info_separator',
            'type' => 'custom_html',
            'value' => '<h4 class="mt-4 mb-3 text-primary">💰 Purchase Information</h4><hr>',
        ]);

        CRUD::addField([
            'name' => 'purchase_price',
            'label' => 'Purchase Price (₹)',
            'type' => 'number',
            'attributes' => ['step' => '0.01', 'min' => '0'],
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'current_value',
            'label' => 'Current Value (₹)',
            'type' => 'number',
            'attributes' => ['step' => '0.01', 'min' => '0'],
            'hint' => 'Current market/book value',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'purchase_date',
            'label' => 'Purchase Date',
            'type' => 'date',
            'wrapper' => ['class' => 'form-group col-md-4'],
        ]);

        CRUD::addField([
            'name' => 'warranty_expiry',
            'label' => 'Warranty Expiry',
            'type' => 'date',
            'wrapper' => ['class' => 'form-group col-md-4'],
        ]);

        CRUD::addField([
            'name' => 'vendor_name',
            'label' => 'Vendor Name',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'vendor_contact',
            'label' => 'Vendor Contact',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        // Assignment Section
        CRUD::addField([
            'name' => 'assignment_separator',
            'type' => 'custom_html',
            'value' => '<h4 class="mt-4 mb-3 text-primary">👤 Assignment Details</h4><hr>',
        ]);

        CRUD::addField([
            'name' => 'assigned_to_employee_id',
            'label' => 'Assign to Employee',
            'type' => 'select',
            'entity' => 'assignedEmployee',
            'attribute' => 'name',
            'model' => "App\\Models\\Employee",
            'allows_null' => true,
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'assigned_to_client_id',
            'label' => 'Assign to Client',
            'type' => 'select',
            'entity' => 'assignedClient',
            'attribute' => 'name',
            'model' => "App\\Models\\Client",
            'allows_null' => true,
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'assigned_date',
            'label' => 'Assignment Date',
            'type' => 'date',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        // Maintenance Section
        CRUD::addField([
            'name' => 'maintenance_separator',
            'type' => 'custom_html',
            'value' => '<h4 class="mt-4 mb-3 text-primary">🔧 Maintenance</h4><hr>',
        ]);

        CRUD::addField([
            'name' => 'next_maintenance_date',
            'label' => 'Next Maintenance Date',
            'type' => 'date',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'maintenance_notes',
            'label' => 'Maintenance Notes',
            'type' => 'textarea',
            'attributes' => ['rows' => 3],
            'wrapper' => ['class' => 'form-group col-md-12'],
        ]);

        // Additional Information
        CRUD::addField([
            'name' => 'additional_separator',
            'type' => 'custom_html',
            'value' => '<h4 class="mt-4 mb-3 text-primary">📝 Additional Information</h4><hr>',
        ]);

        CRUD::addField([
            'name' => 'image_path',
            'label' => 'Asset Image',
            'type' => 'upload',
            'upload' => true,
            'disk' => 'public',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'notes',
            'label' => 'Notes',
            'type' => 'textarea',
            'attributes' => ['rows' => 4],
            'wrapper' => ['class' => 'form-group col-md-12'],
        ]);
    }

    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    /**
     * Define what happens when the Show operation is loaded.
     * 
     * @return void
     */
    protected function setupShowOperation()
    {
        $this->setupListOperation();
        
        CRUD::addColumn([
            'name' => 'description',
            'label' => 'Description',
            'type' => 'text',
        ]);

        CRUD::addColumn([
            'name' => 'model',
            'label' => 'Model',
            'type' => 'text',
        ]);

        CRUD::addColumn([
            'name' => 'serial_number',
            'label' => 'Serial Number',
            'type' => 'text',
        ]);

        CRUD::addColumn([
            'name' => 'current_value',
            'label' => 'Current Value',
            'type' => 'number',
            'prefix' => '₹ ',
            'decimals' => 2,
        ]);

        CRUD::addColumn([
            'name' => 'warranty_expiry',
            'label' => 'Warranty Expiry',
            'type' => 'date',
        ]);

        CRUD::addColumn([
            'name' => 'notes',
            'label' => 'Notes',
            'type' => 'text',
        ]);
    }
}
