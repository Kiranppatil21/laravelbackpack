<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CompanyJobOpeningRequest;
use App\Models\CompanyJobOpening;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class CompanyJobOpeningCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CompanyJobOpeningCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(CompanyJobOpening::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/company-job-openings');
        CRUD::setEntityNameStrings('job opening', 'job openings');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column('title')->type('text')->label('Job Title');
        CRUD::column('department')->type('text');
        CRUD::column('location')->type('text');
        CRUD::column('type')->type('text');
        CRUD::column('status')->type('text');
        CRUD::column('created_at')->type('datetime')->label('Posted Date');
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(CompanyJobOpeningRequest::class);

        CRUD::field('title')
            ->type('text')
            ->label('Job Title')
            ->attributes(['required' => true]);

        CRUD::field('department')
            ->type('text')
            ->label('Department')
            ->attributes(['required' => true]);

        CRUD::field('location')
            ->type('text')
            ->label('Location')
            ->attributes(['required' => true]);

        CRUD::field('description')
            ->type('textarea')
            ->label('Job Description')
            ->attributes(['required' => true, 'rows' => 6]);

        CRUD::field('contact_email')
            ->type('email')
            ->label('Contact Email')
            ->attributes(['required' => true]);

        CRUD::field('status')
            ->type('select_from_array')
            ->label('Status')
            ->options([
                'active' => 'Active',
                'inactive' => 'Inactive',
                'closed' => 'Closed',
            ])
            ->default('active');
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
}