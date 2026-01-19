<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\EmployeeRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\DB;
use Dompdf\Dompdf;

/**
 * Class EmployeeCrudController
 *
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class EmployeeCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Employee::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/employee');
        CRUD::setEntityNameStrings('employee', 'employees');
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
        // Temporarily bypass tenant scope for testing
        $this->crud->addClause('withoutGlobalScope', \App\Models\Scopes\TenantScope::class);
        
        // For the client relationship loading, bypass tenant scope as well
        $this->crud->query->when(true, function($query) {
            return $query->with(['client' => function($q) {
                $q->withoutGlobalScope(\App\Models\Scopes\TenantScope::class);
            }]);
        });
        
        // Custom columns for better display
        CRUD::addColumn([
            'name' => 'name',
            'label' => 'Full Name',
            'type' => 'text',
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhere('first_name', 'like', '%'.$searchTerm.'%')
                      ->orWhere('last_name', 'like', '%'.$searchTerm.'%')
                      ->orWhere(DB::raw('CONCAT(first_name, " ", last_name)'), 'like', '%'.$searchTerm.'%');
            }
        ]);

        CRUD::addColumn([
            'name' => 'email',
            'label' => 'Email',
            'type' => 'email',
        ]);

        CRUD::addColumn([
            'name' => 'phone',
            'label' => 'Phone',
            'type' => 'phone',
        ]);

        CRUD::addColumn([
            'name' => 'client_id',
            'label' => 'Assigned Client', 
            'type' => 'closure',
            'function' => function($entry) {
                if ($entry->client_id) {
                    // Load client without tenant scope
                    $client = \App\Models\Client::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)->find($entry->client_id);
                    if ($client) {
                        return '<span class="badge badge-success">' . $client->name . '</span>';
                    }
                    return '<span class="badge badge-warning">Client #' . $entry->client_id . '</span>';
                }
                return '<span class="badge badge-secondary">Not Assigned</span>';
            },
            'escaped' => false,
        ]);

        CRUD::addColumn([
            'name' => 'job_role',
            'label' => 'Role',
            'type' => 'text',
        ]);

        CRUD::addColumn([
            'name' => 'monthly_salary',
            'label' => 'Monthly Salary',
            'type' => 'number',
            'prefix' => '$',
            'decimals' => 2,
        ]);

        CRUD::addColumn([
            'name' => 'kyc_status',
            'label' => 'KYC Status',
            'type' => 'select_from_array',
            'options' => [
                'pending' => '<span class="badge badge-warning">Pending</span>',
                'in_progress' => '<span class="badge badge-info">In Progress</span>',
                'completed' => '<span class="badge badge-success">Completed</span>',
                'rejected' => '<span class="badge badge-danger">Rejected</span>',
            ],
            'escaped' => false,
        ]);

        CRUD::addColumn([
            'name' => 'created_at',
            'label' => 'Joined Date',
            'type' => 'date',
        ]);

        // Add status column
        CRUD::addColumn([
            'name' => 'status',
            'label' => 'Status',
            'type' => 'closure',
            'function' => function($entry) {
                $status = $entry->status ?? 'active';
                $badge = $status === 'active' ? 'success' : 'danger';
                return '<span class="badge badge-'.$badge.'">'.ucfirst($status).'</span>';
            },
            'escaped' => false,
        ]);

        // Note: Filters require Backpack Pro - removed for compatibility
        // You can add search functionality using the search bar instead

        // Note: Custom buttons removed for compatibility - you can add them back if needed
        // CRUD::addButtonFromView('top', 'bulk_export', 'admin.buttons.bulk_export', 'beginning');
        // CRUD::addButtonFromView('line', 'view_attendance', 'admin.buttons.view_attendance', 'beginning');

        // Enable responsive table
        CRUD::enableResponsiveTable();
        
        // Add pagination options
        CRUD::setDefaultPageLength(25);
        CRUD::enablePersistentTable();

        // Apply tenant scoping if user is not Super Admin
        if (backpack_user() && backpack_user()->hasRole('agency_owner') && backpack_user()->tenant_id) {
            CRUD::addClause('where', 'tenant_id', backpack_user()->tenant_id);
        }
        
        // Add action buttons
        CRUD::addButtonFromModelFunction('line', 'toggle_status', 'getToggleStatusButton', 'beginning');
        CRUD::addButtonFromModelFunction('line', 'id_card', 'getIdCardButton', 'beginning');
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
        CRUD::setValidation(EmployeeRequest::class);

        // Determine the current client assignment so the select stays populated on update
        $currentEntry = $this->crud->getCurrentEntry();
        $selectedClientId = $currentEntry ? $currentEntry->client_id : $this->crud->getRequest()->query('client_id');

        // Set page title
        CRUD::setTitle('Raj Security Services | Add Employee', 'create');
        CRUD::setHeading('Add Employee', 'create');

        // Personal Information Details Section
        CRUD::addField([
            'name' => 'personal_info_separator',
            'type' => 'custom_html',
            'value' => '<h4 class="mt-4 mb-3 text-primary">📝 Personal Information Details</h4><hr>',
        ]);

        CRUD::addField([
            'name' => 'designation',
            'label' => 'Designation',
            'type' => 'select_from_array',
            'options' => $this->getDesignationOptions(),
            'allows_null' => true,
            'wrapper' => ['class' => 'form-group col-md-3'],
        ]);

        CRUD::addField([
            'name' => 'education',
            'label' => 'Education',
            'type' => 'text',
            'attributes' => ['required' => true],
            'wrapper' => ['class' => 'form-group col-md-3'],
        ]);

        CRUD::addField([
            'name' => 'name',
            'label' => 'Name of Employee',
            'type' => 'text',
            'attributes' => ['required' => true],
            'wrapper' => ['class' => 'form-group col-md-3'],
        ]);

        CRUD::addField([
            'name' => 'father_name',
            'label' => 'Father Name',
            'type' => 'text',
            'attributes' => ['required' => true],
            'wrapper' => ['class' => 'form-group col-md-3'],
        ]);

        CRUD::addField([
            'name' => 'nationality',
            'label' => 'Nationality',
            'type' => 'text',
            'default' => 'Indian',
            'attributes' => ['required' => true],
            'wrapper' => ['class' => 'form-group col-md-3'],
        ]);

        CRUD::addField([
            'name' => 'current_address',
            'label' => 'Current Address',
            'type' => 'textarea',
            'attributes' => ['required' => true, 'rows' => 3],
            'wrapper' => ['class' => 'form-group col-md-3'],
        ]);

        CRUD::addField([
            'name' => 'permanent_address',
            'label' => 'Permanent Address',
            'type' => 'textarea',
            'attributes' => ['rows' => 3],
            'wrapper' => ['class' => 'form-group col-md-3'],
        ]);

        CRUD::addField([
            'name' => 'same_address',
            'label' => 'Same as Current Address',
            'type' => 'checkbox',
            'wrapper' => ['class' => 'form-group col-md-12'],
        ]);

        CRUD::addField([
            'name' => 'date_of_birth',
            'label' => 'Date of Birth',
            'type' => 'date',
            'attributes' => ['required' => true],
            'wrapper' => ['class' => 'form-group col-md-3'],
        ]);

        CRUD::addField([
            'name' => 'age',
            'label' => 'Age (Auto-calculated)',
            'type' => 'number',
            'attributes' => ['readonly' => true],
            'wrapper' => ['class' => 'form-group col-md-3'],
        ]);

        CRUD::addField([
            'name' => 'gender',
            'label' => 'Gender',
            'type' => 'select_from_array',
            'options' => [
                'Male' => 'Male',
                'Female' => 'Female',
                'Other' => 'Other',
            ],
            'allows_null' => true,
            'wrapper' => ['class' => 'form-group col-md-3'],
        ]);

        CRUD::addField([
            'name' => 'marital_status',
            'label' => 'Marital Status',
            'type' => 'select_from_array',
            'options' => [
                'Single' => 'Single',
                'Married' => 'Married',
                'Divorced' => 'Divorced',
                'Widowed' => 'Widowed',
            ],
            'allows_null' => true,
            'wrapper' => ['class' => 'form-group col-md-3'],
        ]);

        CRUD::addField([
            'name' => 'email',
            'label' => 'Email',
            'type' => 'email',
            'wrapper' => ['class' => 'form-group col-md-3'],
        ]);

        CRUD::addField([
            'name' => 'phone',
            'label' => 'Contact No',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-3'],
        ]);

        CRUD::addField([
            'name' => 'photo_path',
            'label' => 'Photo Upload',
            'type' => 'upload',
            'upload' => true,
            'disk' => 'public',
            'wrapper' => ['class' => 'form-group col-md-12'],
        ]);

        // Client Assignment Section
        CRUD::addField([
            'name' => 'client_assignment_separator',
            'type' => 'custom_html',
            'value' => '<h4 class="mt-4 mb-3 text-primary">🏢 Assign Employee to Client</h4><hr>',
        ]);

        CRUD::addField([
            'name' => 'client_id',
            'label' => 'Assign to Client',
            'type' => 'select',
            'entity' => 'client',
            'attribute' => 'name',
            'model' => 'App\Models\Client',
            'allows_null' => true,
            'value' => $selectedClientId,
            'wrapper' => ['class' => 'form-group col-md-3'],
            'hint' => 'Select a client to assign this employee to a specific project/site',
        ]);

        CRUD::addField([
            'name' => 'client_assignment_info',
            'type' => 'custom_html',
            'value' => '<div class="col-md-6"><div class="alert alert-info"><i class="la la-info-circle"></i> <strong>Note:</strong> Assigning an employee to a client will make them available for that client\'s projects and attendance tracking.</div></div>',
        ]);

        CRUD::addField([
            'name' => 'position',
            'label' => 'Position/Role',
            'type' => 'select_from_array',
            'options' => [
                'Guard' => 'Security Guard',
                'Supervisor' => 'Supervisor',
                'Field Officer' => 'Field Officer',
                'Manager Staff' => 'Manager Staff',
                'Watchman' => 'Watchman',
                'Security Officer' => 'Security Officer',
                'Team Leader' => 'Team Leader',
            ],
            'allows_null' => true,
            'wrapper' => ['class' => 'form-group col-md-3'],
            'hint' => 'Employee position/role at the assigned client location',
        ]);

        CRUD::addField([
            'name' => 'job_role',
            'label' => 'Job Role Description',
            'type' => 'textarea',
            'attributes' => ['rows' => 3],
            'wrapper' => ['class' => 'form-group col-md-3'],
            'hint' => 'Detailed job responsibilities and duties',
        ]);

        CRUD::addField([
            'name' => 'monthly_salary',
            'label' => 'Monthly Salary (₹)',
            'type' => 'number',
            'attributes' => ['step' => '0.01', 'min' => '0'],
            'wrapper' => ['class' => 'form-group col-md-3'],
            'hint' => 'Employee monthly salary amount',
        ]);

        // Shift Hour Section
        CRUD::addField([
            'name' => 'shift_separator',
            'type' => 'custom_html',
            'value' => '<h4 class="mt-4 mb-3 text-primary">⏰ Shift Hour</h4><hr>',
        ]);

        CRUD::addField([
            'name' => 'shift_hour',
            'label' => 'Shift Hour',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-3'],
        ]);

        // PF/ESIC Details Section
        CRUD::addField([
            'name' => 'pf_esic_separator',
            'type' => 'custom_html',
            'value' => '<h4 class="mt-4 mb-3 text-primary">🏛️ PF/ESIC Details</h4><hr>',
        ]);

        CRUD::addField([
            'name' => 'pf_no',
            'label' => 'PF No',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-3'],
        ]);

        CRUD::addField([
            'name' => 'uan_no',
            'label' => 'UAN No',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-3'],
        ]);

        CRUD::addField([
            'name' => 'esic',
            'label' => 'ESIC',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-3'],
        ]);

        CRUD::addField([
            'name' => 'esic_percentage',
            'label' => 'ESIC Percentage',
            'type' => 'number',
            'attributes' => ['step' => '0.01', 'min' => '0', 'max' => '100'],
            'default' => '0.75',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'pf_percentage',
            'label' => 'PF Percentage',
            'type' => 'number',
            'attributes' => ['step' => '0.01', 'min' => '0', 'max' => '100'],
            'default' => '12.00',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'pt_charges_apply',
            'label' => 'PT Charges Apply',
            'type' => 'checkbox',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        // Bank Account Details Section
        CRUD::addField([
            'name' => 'bank_separator',
            'type' => 'custom_html',
            'value' => '<h4 class="mt-4 mb-3 text-primary">🏦 Bank Account Details</h4><hr>',
        ]);

        CRUD::addField([
            'name' => 'bank_name',
            'label' => 'Bank Name',
            'type' => 'text',
            'attributes' => ['required' => true],
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'bank_branch',
            'label' => 'Branch',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'account_no',
            'label' => 'Account No',
            'type' => 'text',
            'attributes' => ['required' => true],
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'ifsc_code',
            'label' => 'IFSC Code',
            'type' => 'text',
            'attributes' => ['required' => true],
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'bank_phone_no',
            'label' => 'Phone No',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'account_holder_name',
            'label' => 'Account Holder Name',
            'type' => 'text',
            'attributes' => ['required' => true],
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        // Old Company Details Section
        CRUD::addField([
            'name' => 'old_company_separator',
            'type' => 'custom_html',
            'value' => '<h4 class="mt-4 mb-3 text-primary">🏢 Old Company Details</h4><hr>',
        ]);

        CRUD::addField([
            'name' => 'old_company_name',
            'label' => 'Company Name',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'old_company_year',
            'label' => 'Year',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'reason_for_leaving',
            'label' => 'Reason of Leaving',
            'type' => 'textarea',
            'attributes' => ['rows' => 3],
            'wrapper' => ['class' => 'form-group col-md-12'],
        ]);

        // Identity Proofs Section (Dynamic)
        CRUD::addField([
            'name' => 'identity_proofs_separator',
            'type' => 'custom_html',
            'value' => '<h4 class="mt-4 mb-3 text-primary">🆔 Identity Proofs</h4><hr>',
        ]);

        // Identity Proofs Section - Dynamic with Add More buttons
        CRUD::addField([
            'name' => 'identity_proofs_dynamic',
            'type' => 'custom_html',
            'value' => '
                <div id="identity-proofs-container">
                    <div class="identity-proof-item border rounded p-3 mb-3" data-index="0">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Document Type</label>
                                <select name="identity_proofs[0][identity_proof_type]" class="form-select" required>
                                    <option value="">Select Document</option>
                                    <option value="Aadhaar Card">Aadhaar Card</option>
                                    <option value="PAN Card">PAN Card</option>
                                    <option value="Voter ID">Voter ID</option>
                                    <option value="Driving License">Driving License</option>
                                    <option value="Passport">Passport</option>
                                    <option value="Ration Card">Ration Card</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Document Number</label>
                                <input type="text" name="identity_proofs[0][identity_proof_no]" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Upload Document</label>
                                <input type="file" name="identity_proofs[0][image_file]" class="form-control" accept="image/*,application/pdf">
                            </div>
                            <div class="col-md-1">
                                <label>&nbsp;</label><br>
                                <button type="button" class="btn btn-danger btn-sm remove-identity-proof" style="display: none;">×</button>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" id="add-identity-proof" class="btn btn-success btn-sm mb-3">+ Add Identity Proof</button>
            ',
        ]);

        // Family Members Section (Dynamic)
        CRUD::addField([
            'name' => 'family_members_separator',
            'type' => 'custom_html',
            'value' => '<h4 class="mt-4 mb-3 text-primary">👨‍👩‍👧‍👦 Family Members</h4><hr>',
        ]);

        // Family Members Section - Dynamic with Add More buttons
        CRUD::addField([
            'name' => 'family_members_dynamic',
            'type' => 'custom_html',
            'value' => '
                <div id="family-members-container">
                    <div class="family-member-item border rounded p-3 mb-3" data-index="0">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Name</label>
                                <input type="text" name="family_members[0][name]" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label>Relationship</label>
                                <select name="family_members[0][relationship]" class="form-control" required>
                                    <option value="">Select Relationship</option>
                                    <option value="Father">Father</option>
                                    <option value="Mother">Mother</option>
                                    <option value="Spouse">Spouse</option>
                                    <option value="Son">Son</option>
                                    <option value="Daughter">Daughter</option>
                                    <option value="Brother">Brother</option>
                                    <option value="Sister">Sister</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Age</label>
                                <input type="number" name="family_members[0][age]" class="form-control" min="0">
                            </div>
                            <div class="col-md-2">
                                <label>Phone</label>
                                <input type="text" name="family_members[0][phone_no]" class="form-control">
                            </div>
                            <div class="col-md-1">
                                <label>&nbsp;</label><br>
                                <button type="button" class="btn btn-danger btn-sm remove-family-member" style="display: none;">×</button>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" id="add-family-member" class="btn btn-success btn-sm mb-3">+ Add Family Member</button>
            ',
        ]);

        // Acquaintances Section (Dynamic)
        CRUD::addField([
            'name' => 'acquaintances_separator',
            'type' => 'custom_html',
            'value' => '<h4 class="mt-4 mb-3 text-primary">🤝 Emergency Contacts / Acquaintances</h4><hr>',
        ]);

        // Acquaintances Section - Dynamic with Add More buttons
        CRUD::addField([
            'name' => 'acquaintances_dynamic',
            'type' => 'custom_html',
            'value' => '
                <div id="acquaintances-container">
                    <div class="acquaintance-item border rounded p-3 mb-3" data-index="0">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Name</label>
                                <input type="text" name="acquaintances[0][name]" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label>Relationship</label>
                                <select name="acquaintances[0][relationship]" class="form-control" required>
                                    <option value="">Select Relationship</option>
                                    <option value="emergency_contact">Emergency Contact</option>
                                    <option value="reference">Reference</option>
                                    <option value="friend">Friend</option>
                                    <option value="relative">Relative</option>
                                    <option value="neighbor">Neighbor</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Phone</label>
                                <input type="text" name="acquaintances[0][phone]" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label>Address</label>
                                <textarea name="acquaintances[0][address]" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-md-1">
                                <label>&nbsp;</label><br>
                                <button type="button" class="btn btn-danger btn-sm remove-acquaintance" style="display: none;">×</button>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" id="add-acquaintance" class="btn btn-success btn-sm mb-3">+ Add Contact</button>
            ',
        ]);

        // Uniform Allocations Section (Dynamic)
        CRUD::addField([
            'name' => 'uniform_allocations_separator',
            'type' => 'custom_html',
            'value' => '<h4 class="mt-4 mb-3 text-primary">👕 Uniform Allocations</h4><hr>',
        ]);

        // Uniform Allocations Section - Dynamic with Add More buttons
        CRUD::addField([
            'name' => 'uniform_allocations_dynamic',
            'type' => 'custom_html',
            'value' => '
                <div id="uniforms-container">
                    <div class="uniform-item border rounded p-3 mb-3" data-index="0">
                        <div class="row">
                            <div class="col-md-2">
                                <label>Item Type</label>
                                <select name="uniforms[0][item_type]" class="form-control" required>
                                    <option value="">Select Type</option>
                                    <option value="shirt">Shirt</option>
                                    <option value="pants">Pants</option>
                                    <option value="jacket">Jacket</option>
                                    <option value="cap">Cap</option>
                                    <option value="belt">Belt</option>
                                    <option value="shoes">Shoes</option>
                                    <option value="badge">Badge</option>
                                    <option value="epaulets">Epaulets</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Size</label>
                                <input type="text" name="uniforms[0][size]" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label>Date Issued</label>
                                <input type="date" name="uniforms[0][date_issued]" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label>Condition</label>
                                <select name="uniforms[0][condition]" class="form-control">
                                    <option value="new">New</option>
                                    <option value="good">Good</option>
                                    <option value="fair">Fair</option>
                                    <option value="poor">Poor</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Notes</label>
                                <input type="text" name="uniforms[0][notes]" class="form-control" placeholder="Replacement, additional item, etc.">
                            </div>
                            <div class="col-md-1">
                                <label>&nbsp;</label><br>
                                <button type="button" class="btn btn-danger btn-sm remove-uniform" style="display: none;">×</button>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" id="add-uniform" class="btn btn-success btn-sm mb-3">+ Add Uniform Item</button>
            ',
        ]);

        // Add JavaScript for form interactions at the end
        $this->addFormJavaScript();
    }

    /**
     * Get designation options from a master table or predefined list
     */
    private function getDesignationOptions()
    {
        return [
            'Security Guard' => 'Security Guard',
            'Supervisor' => 'Supervisor',
            'Manager' => 'Manager',
            'Officer' => 'Officer',
            'Executive' => 'Executive',
            'Watchman' => 'Watchman',
            'Bouncer' => 'Bouncer',
        ];
    }

    /**
     * Add JavaScript for form interactions
     */
    private function addFormJavaScript()
    {
        CRUD::addField([
            'name' => 'form_javascript',
            'type' => 'custom_html',
            'value' => '
                <script>
                console.log("Loading dynamic forms JavaScript...");
                
                // Use event delegation for better reliability with Backpack
                document.addEventListener("click", function(e) {
                    if (e.target.id === "add-identity-proof") {
                        e.preventDefault();
                        addDynamicItem("identity-proofs-container", "identity-proof-item", "remove-identity-proof");
                    }
                    else if (e.target.id === "add-family-member") {
                        e.preventDefault();
                        addDynamicItem("family-members-container", "family-member-item", "remove-family-member");
                    }
                    else if (e.target.id === "add-acquaintance") {
                        e.preventDefault();
                        addDynamicItem("acquaintances-container", "acquaintance-item", "remove-acquaintance");
                    }
                    else if (e.target.id === "add-uniform") {
                        e.preventDefault();
                        addDynamicItem("uniforms-container", "uniform-item", "remove-uniform");
                    }
                    else if (e.target.classList.contains("remove-identity-proof") || 
                             e.target.classList.contains("remove-family-member") || 
                             e.target.classList.contains("remove-acquaintance") || 
                             e.target.classList.contains("remove-uniform")) {
                        e.preventDefault();
                        removeDynamicItem(e.target);
                    }
                });
                
                function addDynamicItem(containerId, itemClass, removeClass) {
                    const container = document.getElementById(containerId);
                    if (!container) return;
                    
                    const template = container.querySelector("." + itemClass);
                    if (!template) return;
                    
                    const newItem = template.cloneNode(true);
                    const currentIndex = container.children.length;
                    
                    newItem.querySelectorAll("input, select, textarea").forEach(field => {
                        if (field.name && field.name.includes("[0]")) {
                            field.name = field.name.replace("[0]", "[" + currentIndex + "]");
                            if (field.type === "date") {
                                field.value = new Date().toISOString().split("T")[0];
                            } else if (field.type !== "file") {
                                field.value = "";
                            }
                        }
                    });
                    
                    const removeBtn = newItem.querySelector("." + removeClass);
                    if (removeBtn) {
                        removeBtn.style.display = "inline-block";
                    }
                    
                    container.appendChild(newItem);
                    updateRemoveButtonVisibility(containerId, removeClass);
                    console.log("Added item to", containerId);
                }
                
                function removeDynamicItem(removeBtn) {
                    const item = removeBtn.closest(".identity-proof-item, .family-member-item, .acquaintance-item, .uniform-item");
                    if (!item) return;
                    
                    const container = item.parentElement;
                    if (container && container.children.length > 1) {
                        item.remove();
                        console.log("Removed item from", container.id);
                    }
                }
                
                function updateRemoveButtonVisibility(containerId, removeClass) {
                    const container = document.getElementById(containerId);
                    if (!container) return;
                    
                    const removeButtons = container.querySelectorAll("." + removeClass);
                    removeButtons.forEach(btn => {
                        btn.style.display = container.children.length === 1 ? "none" : "inline-block";
                    });
                }
                
                // Initialize when page loads
                setTimeout(function() {
                    updateRemoveButtonVisibility("identity-proofs-container", "remove-identity-proof");
                    updateRemoveButtonVisibility("family-members-container", "remove-family-member");
                    updateRemoveButtonVisibility("acquaintances-container", "remove-acquaintance");
                    updateRemoveButtonVisibility("uniforms-container", "remove-uniform");
                    console.log("Dynamic forms ready!");
                }, 1500);
                </script>
                <style>
                /* Make dynamic item blocks slightly tighter */
                .identity-proof-item, .family-member-item, .acquaintance-item, .uniform-item {
                    background-color: #f8f9fa;
                    transition: all 0.18s ease;
                    border: 1px solid #dee2e6;
                    margin-bottom: 6px;
                    padding: 0.5rem;
                }
                .identity-proof-item:hover, .family-member-item:hover, .acquaintance-item:hover, .uniform-item:hover {
                    background-color: #e9ecef;
                }

                /* Reduce default vertical spacing between Backpack form groups to avoid excessive scrolling */
                .form-group {
                    margin-bottom: 0.45rem !important;
                    padding-bottom: 0;
                }

                /* Make 4-column layout fields slightly more compact */
                .form-group.col-md-3 {
                    padding-left: 0.45rem;
                    padding-right: 0.45rem;
                }

                /* Treat existing col-md-6 as 4-column on medium+ screens to avoid editing every field */
                @media (min-width: 768px) {
                    .form-group.col-md-6 {
                        -ms-flex: 0 0 25% !important;
                        flex: 0 0 25% !important;
                        max-width: 25% !important;
                    }
                }

                /* Reduce input vertical padding for compactness */
                .form-control, .form-select, .form-control-file {
                    padding: 0.38rem 0.5rem;
                    height: calc(1.6em + 0.76rem);
                }

                /* Smaller remove buttons */
                .btn-danger.btn-sm { padding: 0.15rem 0.45rem; font-size: 1rem; line-height: 1; }
                </style>
            ',
        ]);
    }

    /**
     * Define what happens when the Update operation is loaded.
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
        
        // Load existing dynamic data for the employee
        $employee = $this->crud->getCurrentEntry();
        if ($employee) {
            // Load related data
            $identityProofs = $employee->identityProofs()->get()->toArray();
            $familyMembers = $employee->familyMembers()->get()->toArray();
            $acquaintances = $employee->acquaintances()->get()->map(function($item) {
                $details = json_decode($item->details, true);
                return [
                    'id' => $item->id,
                    'name' => $details['name'] ?? '',
                    'relationship' => $details['relationship'] ?? '',
                    'phone' => $details['phone'] ?? '',
                    'address' => $details['address'] ?? '',
                ];
            })->toArray();
            $uniformAllocations = $employee->uniformAllocations()->get()->toArray();
            
            // Add JavaScript to populate existing data
            CRUD::addField([
                'name' => 'populate_existing_data',
                'type' => 'custom_html',
                'value' => '<script>
                    // Existing data from database
                    var existingIdentityProofs = ' . json_encode($identityProofs) . ';
                    var existingFamilyMembers = ' . json_encode($familyMembers) . ';
                    var existingAcquaintances = ' . json_encode($acquaintances) . ';
                    var existingUniforms = ' . json_encode($uniformAllocations) . ';
                    
                    // Wait for DOM to be ready
                    setTimeout(function() {
                        populateExistingData();
                    }, 2000);
                    
                    function populateExistingData() {
                        console.log("Populating existing data...");
                        
                        // Populate Identity Proofs
                        populateIdentityProofs();
                        
                        // Populate Family Members
                        populateFamilyMembers();
                        
                        // Populate Acquaintances
                        populateAcquaintances();
                        
                        // Populate Uniforms
                        populateUniforms();
                    }
                    
                    function populateIdentityProofs() {
                        var container = document.getElementById("identity-proofs-container");
                        if (!container || existingIdentityProofs.length === 0) return;
                        
                        container.innerHTML = "";
                        existingIdentityProofs.forEach(function(proof, index) {
                            var html = `
                                <div class="identity-proof-item border rounded p-3 mb-3" data-index="${index}">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label">Document Type</label>
                                            <select name="identity_proofs[${index}][identity_proof_type]" class="form-select" required>
                                                <option value="">Select Document</option>
                                                <option value="Aadhaar Card" ${proof.identity_proof_type === "Aadhaar Card" ? "selected" : ""}>Aadhaar Card</option>
                                                <option value="PAN Card" ${proof.identity_proof_type === "PAN Card" ? "selected" : ""}>PAN Card</option>
                                                <option value="Voter ID" ${proof.identity_proof_type === "Voter ID" ? "selected" : ""}>Voter ID</option>
                                                <option value="Driving License" ${proof.identity_proof_type === "Driving License" ? "selected" : ""}>Driving License</option>
                                                <option value="Passport" ${proof.identity_proof_type === "Passport" ? "selected" : ""}>Passport</option>
                                                <option value="Ration Card" ${proof.identity_proof_type === "Ration Card" ? "selected" : ""}>Ration Card</option>
                                                <option value="Other" ${proof.identity_proof_type === "Other" ? "selected" : ""}>Other</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Document Number</label>
                                            <input type="text" name="identity_proofs[${index}][identity_proof_no]" class="form-control" value="${proof.identity_proof_no || ""}" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Upload Document</label>
                                            <input type="file" name="identity_proofs[${index}][image_file]" class="form-control" accept="image/*,application/pdf">
                                            ${proof.image_path ? `<small class="text-muted">Current: <a href="/storage/${proof.image_path}" target="_blank">View File</a></small>` : ""}
                                        </div>
                                        <div class="col-md-1">
                                            <label>&nbsp;</label><br>
                                            <button type="button" class="btn btn-danger btn-sm remove-identity-proof" ${index === 0 ? "style=\"display: none;\"" : ""}>×</button>
                                        </div>
                                    </div>
                                </div>
                            `;
                            container.insertAdjacentHTML("beforeend", html);
                        });
                    }
                    
                    function populateFamilyMembers() {
                        var container = document.getElementById("family-members-container");
                        if (!container || existingFamilyMembers.length === 0) return;
                        
                        container.innerHTML = "";
                        existingFamilyMembers.forEach(function(member, index) {
                            var html = `
                                <div class="family-member-item border rounded p-3 mb-3" data-index="${index}">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>Name</label>
                                            <input type="text" name="family_members[${index}][name]" class="form-control" value="${member.name || ""}" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Relationship</label>
                                            <select name="family_members[${index}][relationship]" class="form-control" required>
                                                <option value="">Select Relationship</option>
                                                <option value="Father" ${member.relationship === "Father" ? "selected" : ""}>Father</option>
                                                <option value="Mother" ${member.relationship === "Mother" ? "selected" : ""}>Mother</option>
                                                <option value="Spouse" ${member.relationship === "Spouse" ? "selected" : ""}>Spouse</option>
                                                <option value="Son" ${member.relationship === "Son" ? "selected" : ""}>Son</option>
                                                <option value="Daughter" ${member.relationship === "Daughter" ? "selected" : ""}>Daughter</option>
                                                <option value="Brother" ${member.relationship === "Brother" ? "selected" : ""}>Brother</option>
                                                <option value="Sister" ${member.relationship === "Sister" ? "selected" : ""}>Sister</option>
                                                <option value="Other" ${member.relationship === "Other" ? "selected" : ""}>Other</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label>Age</label>
                                            <input type="number" name="family_members[${index}][age]" class="form-control" min="0" value="${member.age || ""}">
                                        </div>
                                        <div class="col-md-2">
                                            <label>Phone</label>
                                            <input type="text" name="family_members[${index}][phone_no]" class="form-control" value="${member.phone_no || ""}">
                                        </div>
                                        <div class="col-md-1">
                                            <label>&nbsp;</label><br>
                                            <button type="button" class="btn btn-danger btn-sm remove-family-member" ${index === 0 ? "style=\"display: none;\"" : ""}>×</button>
                                        </div>
                                    </div>
                                </div>
                            `;
                            container.insertAdjacentHTML("beforeend", html);
                        });
                    }
                    
                    function populateAcquaintances() {
                        var container = document.getElementById("acquaintances-container");
                        if (!container || existingAcquaintances.length === 0) return;
                        
                        container.innerHTML = "";
                        existingAcquaintances.forEach(function(contact, index) {
                            var html = `
                                <div class="acquaintance-item border rounded p-3 mb-3" data-index="${index}">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label>Name</label>
                                            <input type="text" name="acquaintances[${index}][name]" class="form-control" value="${contact.name || ""}" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Relationship</label>
                                            <select name="acquaintances[${index}][relationship]" class="form-control" required>
                                                <option value="">Select Relationship</option>
                                                <option value="emergency_contact" ${contact.relationship === "emergency_contact" ? "selected" : ""}>Emergency Contact</option>
                                                <option value="reference" ${contact.relationship === "reference" ? "selected" : ""}>Reference</option>
                                                <option value="friend" ${contact.relationship === "friend" ? "selected" : ""}>Friend</option>
                                                <option value="relative" ${contact.relationship === "relative" ? "selected" : ""}>Relative</option>
                                                <option value="neighbor" ${contact.relationship === "neighbor" ? "selected" : ""}>Neighbor</option>
                                                <option value="other" ${contact.relationship === "other" ? "selected" : ""}>Other</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label>Phone</label>
                                            <input type="text" name="acquaintances[${index}][phone]" class="form-control" value="${contact.phone || ""}">
                                        </div>
                                        <div class="col-md-3">
                                            <label>Address</label>
                                            <textarea name="acquaintances[${index}][address]" class="form-control" rows="2">${contact.address || ""}</textarea>
                                        </div>
                                        <div class="col-md-1">
                                            <label>&nbsp;</label><br>
                                            <button type="button" class="btn btn-danger btn-sm remove-acquaintance" ${index === 0 ? "style=\"display: none;\"" : ""}>×</button>
                                        </div>
                                    </div>
                                </div>
                            `;
                            container.insertAdjacentHTML("beforeend", html);
                        });
                    }
                    
                    function populateUniforms() {
                        var container = document.getElementById("uniforms-container");
                        if (!container || existingUniforms.length === 0) return;
                        
                        container.innerHTML = "";
                        existingUniforms.forEach(function(uniform, index) {
                            var html = `
                                <div class="uniform-item border rounded p-3 mb-3" data-index="${index}">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>Item Type</label>
                                            <select name="uniforms[${index}][item_type]" class="form-control" required>
                                                <option value="">Select Type</option>
                                                <option value="shirt" ${uniform.item_type === "shirt" ? "selected" : ""}>Shirt</option>
                                                <option value="pants" ${uniform.item_type === "pants" ? "selected" : ""}>Pants</option>
                                                <option value="jacket" ${uniform.item_type === "jacket" ? "selected" : ""}>Jacket</option>
                                                <option value="cap" ${uniform.item_type === "cap" ? "selected" : ""}>Cap</option>
                                                <option value="belt" ${uniform.item_type === "belt" ? "selected" : ""}>Belt</option>
                                                <option value="shoes" ${uniform.item_type === "shoes" ? "selected" : ""}>Shoes</option>
                                                <option value="badge" ${uniform.item_type === "badge" ? "selected" : ""}>Badge</option>
                                                <option value="epaulets" ${uniform.item_type === "epaulets" ? "selected" : ""}>Epaulets</option>
                                                <option value="other" ${uniform.item_type === "other" ? "selected" : ""}>Other</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label>Size</label>
                                            <input type="text" name="uniforms[${index}][size]" class="form-control" value="${uniform.size || ""}">
                                        </div>
                                        <div class="col-md-2">
                                            <label>Date Issued</label>
                                            <input type="date" name="uniforms[${index}][date_issued]" class="form-control" value="${uniform.date_issued || ""}">
                                        </div>
                                        <div class="col-md-2">
                                            <label>Condition</label>
                                            <select name="uniforms[${index}][condition]" class="form-control">
                                                <option value="new" ${uniform.condition === "new" ? "selected" : ""}>New</option>
                                                <option value="good" ${uniform.condition === "good" ? "selected" : ""}>Good</option>
                                                <option value="fair" ${uniform.condition === "fair" ? "selected" : ""}>Fair</option>
                                                <option value="poor" ${uniform.condition === "poor" ? "selected" : ""}>Poor</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Notes</label>
                                            <input type="text" name="uniforms[${index}][notes]" class="form-control" value="${uniform.notes || ""}" placeholder="Replacement, additional item, etc.">
                                        </div>
                                        <div class="col-md-1">
                                            <label>&nbsp;</label><br>
                                            <button type="button" class="btn btn-danger btn-sm remove-uniform" ${index === 0 ? "style=\"display: none;\"" : ""}>×</button>
                                        </div>
                                    </div>
                                </div>
                            `;
                            container.insertAdjacentHTML("beforeend", html);
                        });
                    }
                </script>',
            ]);
        }
    }

    /**
     * Define what happens when the Show operation is loaded.
     */
    protected function setupShowOperation()
    {
        $this->setupListOperation();
        
        // Add additional show columns
        CRUD::addColumn([
            'name' => 'identity_proofs_display',
            'label' => 'Identity Proofs',
            'type' => 'closure',
            'function' => function($entry) {
                $proofs = $entry->identityProofs;
                if ($proofs->isEmpty()) {
                    return '<span class=\"badge badge-secondary\">No proofs added</span>';
                }
                $html = '<ul class=\"list-unstyled mb-0\">';
                foreach ($proofs as $proof) {
                    $html .= '<li class=\"mb-2\">';
                    $html .= '<strong>' . $proof->identity_proof_type . ':</strong> ' . $proof->identity_proof_no;
                    if ($proof->image_path) {
                        $html .= ' <a href=\"/storage/' . $proof->image_path . '\" target=\"_blank\" class=\"btn btn-xs btn-info\"><i class=\"la la-eye\"></i> View</a>';
                    }
                    $html .= '</li>';
                }
                $html .= '</ul>';
                return $html;
            },
            'escaped' => false,
        ]);
        
        CRUD::addColumn([
            'name' => 'family_members_display',
            'label' => 'Family Members',
            'type' => 'closure',
            'function' => function($entry) {
                $members = $entry->familyMembers;
                if ($members->isEmpty()) {
                    return '<span class=\"badge badge-secondary\">No family members added</span>';
                }
                $html = '<ul class=\"list-unstyled mb-0\">';
                foreach ($members as $member) {
                    $html .= '<li class=\"mb-1\">';
                    $html .= '<strong>' . $member->name . '</strong> (' . $member->relationship . ')';
                    if ($member->age) $html .= ' - Age: ' . $member->age;
                    if ($member->phone_no) $html .= ' - Phone: ' . $member->phone_no;
                    $html .= '</li>';
                }
                $html .= '</ul>';
                return $html;
            },
            'escaped' => false,
        ]);
        
        CRUD::addColumn([
            'name' => 'acquaintances_display',
            'label' => 'Emergency Contacts',
            'type' => 'closure',
            'function' => function($entry) {
                $contacts = $entry->acquaintances;
                if ($contacts->isEmpty()) {
                    return '<span class=\"badge badge-secondary\">No contacts added</span>';
                }
                $html = '<ul class=\"list-unstyled mb-0\">';
                foreach ($contacts as $contact) {
                    $details = json_decode($contact->details, true);
                    if ($details) {
                        $html .= '<li class=\"mb-2\">';
                        $html .= '<strong>' . ($details['name'] ?? 'N/A') . '</strong> (' . ($details['relationship'] ?? 'N/A') . ')<br>';
                        if (!empty($details['phone'])) $html .= 'Phone: ' . $details['phone'] . '<br>';
                        if (!empty($details['address'])) $html .= 'Address: ' . $details['address'];
                        $html .= '</li>';
                    }
                }
                $html .= '</ul>';
                return $html;
            },
            'escaped' => false,
        ]);
        
        CRUD::addColumn([
            'name' => 'uniforms_display',
            'label' => 'Uniform Allocations',
            'type' => 'closure',
            'function' => function($entry) {
                $uniforms = $entry->uniformAllocations;
                if ($uniforms->isEmpty()) {
                    return '<span class=\"badge badge-secondary\">No uniforms allocated</span>';
                }
                $html = '<table class=\"table table-sm table-bordered mb-0\">';
                $html .= '<thead><tr><th>Item</th><th>Size</th><th>Date Issued</th><th>Condition</th></tr></thead>';
                $html .= '<tbody>';
                foreach ($uniforms as $uniform) {
                    $html .= '<tr>';
                    $html .= '<td>' . ucfirst($uniform->item_type) . '</td>';
                    $html .= '<td>' . ($uniform->size ?? 'N/A') . '</td>';
                    $html .= '<td>' . ($uniform->date_issued ? date('d-M-Y', strtotime($uniform->date_issued)) : 'N/A') . '</td>';
                    $html .= '<td><span class=\"badge badge-' . ($uniform->condition === 'new' ? 'success' : 'info') . '\">' . ucfirst($uniform->condition) . '</span></td>';
                    $html .= '</tr>';
                }
                $html .= '</tbody></table>';
                return $html;
            },
            'escaped' => false,
        ]);
    }

    /**
     * Generate ID Card for single employee
     */
    public function generateIdCard($id)
    {
        $employee = $this->crud->getEntry($id);
        
        if (!$employee) {
            \Alert::error('Employee not found.')->flash();
            return redirect()->back();
        }

        // Generate PDF ID Card
        $pdf = $this->createIdCardPdf($employee);
        
        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="employee_id_card_' . $employee->id . '.pdf"',
        ]);
    }

    /**
     * Get employee data for preview (JSON response)
     */
    public function getPreviewData($id)
    {
        $employee = $this->crud->getEntry($id);
        
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        // Load employee with relationships
        $employee->load('agency');
        
        return response()->json($employee);
    }

    /**
     * Preview ID Card design
     */
    public function previewIdCard($id)
    {
        $employee = $this->crud->getEntry($id);
        
        if (!$employee) {
            \Alert::error('Employee not found.')->flash();
            return redirect()->back();
        }

        // Load employee with relationships
        $employee->load('agency');
        
        return view('admin.employee.id_card_preview', compact('employee'));
    }

    /**
     * Generate ID Cards for multiple employees
     */
    public function bulkGenerateIdCards(\Illuminate\Http\Request $request)
    {
        $employeeIds = $request->get('entries', []);
        
        if (empty($employeeIds)) {
            \Alert::error('Please select at least one employee.')->flash();
            return redirect()->back();
        }

        $employees = $this->crud->model->whereIn('id', $employeeIds)->get();
        
        if ($employees->isEmpty()) {
            \Alert::error('No employees found.')->flash();
            return redirect()->back();
        }

        // Generate combined PDF with all ID cards
        $pdf = $this->createBulkIdCardsPdf($employees);
        
        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="employee_id_cards_bulk.pdf"',
        ]);
    }

    /**
     * Create PDF for single employee ID card using new design
     */
    private function createIdCardPdf($employee)
    {
        // Ensure employee has agency relationship loaded
        if (!$employee->relationLoaded('agency')) {
            $employee->load('agency');
        }
        
        $dompdf = new \Dompdf\Dompdf();
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('chroot', storage_path('app/public'));
        $dompdf->setOptions($options);
        
        // Use PDF-specific view without Backpack layout
        $html = view('admin.employee.id_card_pdf', compact('employee'))->render();
        $dompdf->loadHtml($html);
        $dompdf->setPaper([0, 0, 280, 440], 'portrait'); // ID card size
        $dompdf->render();
        
        return $dompdf;
    }

    /**
     * Create PDF for multiple employee ID cards
     */
    private function createBulkIdCardsPdf($employees)
    {
        $dompdf = new \Dompdf\Dompdf();
        $html = '';
        
        foreach ($employees as $index => $employee) {
            $html .= view('admin.employee.id_card', compact('employee'))->render();
            if ($index < count($employees) - 1) {
                $html .= '<div style="page-break-after: always;"></div>';
            }
        }
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper([0, 0, 252, 396], 'portrait'); // ID Card size
        $dompdf->render();
        
        return $dompdf;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store()
    {
        $this->crud->hasAccessOrFail('create');
        $request = $this->crud->validateRequest();
        $item = $this->crud->create($this->crud->getStrippedSaveRequest($request));
        $this->data['entry'] = $this->crud->entry = $item;
        $this->storeDynamicData($item, $request);
        \Alert::success(trans('backpack::crud.insert_success'))->flash();
        $this->crud->setSaveAction();
        return $this->crud->performSaveAction($item->getKey());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update()
    {
        $this->crud->hasAccessOrFail('update');
        $request = $this->crud->validateRequest();
        $item = $this->crud->update($request->get($this->crud->model->getKeyName()),
                            $this->crud->getStrippedSaveRequest($request));
        $this->data['entry'] = $this->crud->entry = $item;
        $this->storeDynamicData($item, $request);
        \Alert::success(trans('backpack::crud.update_success'))->flash();
        $this->crud->setSaveAction();
        return $this->crud->performSaveAction($item->getKey());
    }

    /**
     * Get tenant UUID with proper fallbacks
     */
    private function getTenantUuid($employee)
    {
        if (isset($employee->tenant_uuid) && $employee->tenant_uuid) {
            return $employee->tenant_uuid;
        }
        if (function_exists('tenant') && tenant()) {
            return tenant()->id;
        }
        if (backpack_user() && backpack_user()->tenant_id) {
            return backpack_user()->tenant_id;
        }
        return 'development-uuid';
    }

    /**
     * Store dynamic related data from form arrays
     */
    private function storeDynamicData($employee, $request)
    {
        $tenantUuid = $this->getTenantUuid($employee);
        
        // Store Identity Proofs - Handle array format from custom HTML
        if ($request->has('identity_proofs') && is_array($request->identity_proofs)) {
            $employee->identityProofs()->delete();
            foreach ($request->identity_proofs as $index => $proof) {
                if (!empty($proof['identity_proof_type']) && !empty($proof['identity_proof_no'])) {
                    $imagePath = null;
                    
                    // Handle file upload
                    if ($request->hasFile("identity_proofs.{$index}.image_file")) {
                        $file = $request->file("identity_proofs.{$index}.image_file");
                        $imagePath = $file->store('identity_proofs', 'public');
                    }
                    
                    $employee->identityProofs()->create([
                        'identity_proof_type' => $proof['identity_proof_type'],
                        'identity_proof_no' => $proof['identity_proof_no'],
                        'image_path' => $imagePath,
                        'tenant_uuid' => $tenantUuid,
                    ]);
                }
            }
        }
        
        // Store Family Members - Handle array format from custom HTML
        if ($request->has('family_members') && is_array($request->family_members)) {
            $employee->familyMembers()->delete();
            foreach ($request->family_members as $member) {
                if (!empty($member['name']) && !empty($member['relationship'])) {
                    $employee->familyMembers()->create([
                        'name' => $member['name'],
                        'relationship' => $member['relationship'],
                        'age' => isset($member['age']) && is_numeric($member['age']) ? (int)$member['age'] : 0,
                        'phone_no' => $member['phone_no'] ?? null,
                        'is_nominee' => false,
                        'tenant_uuid' => $tenantUuid,
                    ]);
                }
            }
        }
        
        // Store Acquaintances - Handle array format from custom HTML
        if ($request->has('acquaintances') && is_array($request->acquaintances)) {
            $employee->acquaintances()->delete();
            foreach ($request->acquaintances as $acquaintance) {
                if (!empty($acquaintance['name']) && !empty($acquaintance['relationship'])) {
                    $details = [
                        'name' => $acquaintance['name'],
                        'relationship' => $acquaintance['relationship'],
                        'phone' => $acquaintance['phone'] ?? '',
                        'address' => $acquaintance['address'] ?? '',
                    ];
                    $employee->acquaintances()->create([
                        'details' => json_encode($details),
                        'tenant_uuid' => $tenantUuid,
                    ]);
                }
            }
        }
        
        // Store Uniforms - Handle array format from custom HTML
        if ($request->has('uniforms') && is_array($request->uniforms)) {
            $employee->uniformAllocations()->delete();
            foreach ($request->uniforms as $uniform) {
                if (!empty($uniform['item_type'])) {
                    $employee->uniformAllocations()->create([
                        'item_type' => $uniform['item_type'],
                        'size' => $uniform['size'] ?? null,
                        'date_issued' => !empty($uniform['date_issued']) ? $uniform['date_issued'] : null,
                        'condition' => $uniform['condition'] ?? 'new',
                        'notes' => $uniform['notes'] ?? null,
                        'tenant_uuid' => $tenantUuid,
                    ]);
                }
            }
        }
    }

    /**
     * API endpoint to get clients for bulk assignment
     */
    public function getClientsApi()
    {
        $clients = \App\Models\Client::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
            
        return response()->json($clients);
    }

    /**
     * API endpoint to get employees for bulk assignment
     */
    public function getEmployeesApi()
    {
        $employees = \App\Models\Employee::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->with(['client:id,name'])
            ->select('id', 'name', 'client_id')
            ->orderBy('name')
            ->get();
            
        return response()->json($employees);
    }

    /**
     * Bulk assign employees to client
     */
    public function bulkAssignClient(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id'
        ]);

        try {
            \App\Models\Employee::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
                ->whereIn('id', $request->employee_ids)
                ->update(['client_id' => $request->client_id]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully assigned ' . count($request->employee_ids) . ' employees to client'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error assigning employees: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle employee active/inactive status
     */
    public function toggleStatus($id)
    {
        try {
            // Bypass tenant scope to find employee
            $employee = \App\Models\Employee::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
                ->findOrFail($id);
            
            $currentStatus = $employee->status ?? 'active';
            $newStatus = $currentStatus === 'active' ? 'inactive' : 'active';
            
            $employee->status = $newStatus;
            $employee->save();

            return response()->json([
                'success' => true,
                'message' => 'Employee ' . ($newStatus === 'active' ? 'activated' : 'deactivated') . ' successfully',
                'status' => $newStatus
            ]);
        } catch (\Exception $e) {
            \Log::error('Toggle status error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error toggling status: ' . $e->getMessage()
            ], 500);
        }
    }
}