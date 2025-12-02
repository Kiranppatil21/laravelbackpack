<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\PayrollRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class PayrollCrudController
 *
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class PayrollCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Payroll::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/payroll');
        CRUD::setEntityNameStrings('payroll', 'payrolls');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     *
     * @return void
     */
    protected function setupListOperation()
    {
        // Manual column setup instead of setFromDb() to avoid errors
        CRUD::column('id')->label('ID');
        CRUD::column('employee_id')->label('Employee ID');
        CRUD::column('salary_amount')->label('Salary Amount');
        CRUD::column('pay_period')->label('Pay Period');
        CRUD::column('created_at')->label('Created At')->type('datetime');
        CRUD::column('updated_at')->label('Updated At')->type('datetime');
        
        // Add a per-row "Generate Payslip" button that calls the admin action
        $this->crud->addButtonFromView('line', 'generate_payslip', 'generate_payslip', 'beginning');
    }

    /**
     * Define what happens when the Show operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-show
     *
     * @return void
     */
    protected function setupShowOperation()
    {
        // Show the same columns as list
        $this->setupListOperation();
        
        // Temporarily commented out to isolate button view issue
        // $this->crud->addButtonFromView('top', 'generate_payslip_show', 'generate_payslip_show', 'beginning');
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     *
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(PayrollRequest::class);
        
        // Manual field setup instead of setFromDb() to avoid errors
        CRUD::field('employee_id')->label('Employee ID')->type('number');
        CRUD::field('salary_amount')->label('Salary Amount')->type('number')->attributes(['step' => '0.01']);
        CRUD::field('pay_period')->label('Pay Period')->type('text');
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     *
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    /**
     * Generate a payslip for a specific payroll record
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function generatePayslip($id)
    {
        try {
            $payroll = \App\Models\Payroll::findOrFail($id);
            
            // For now, return a simple view with payroll data
            // This can be enhanced to generate actual PDF payslips
            return view('payslips.generate', compact('payroll'));
            
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate payslip: ' . $e->getMessage());
        }
    }
}
