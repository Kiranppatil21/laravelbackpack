<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\EmployeeRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\DB;

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
            'type' => 'select',
            'entity' => 'client',
            'attribute' => 'name',
            'model' => \App\Models\Client::class,
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
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'education',
            'label' => 'Education',
            'type' => 'text',
            'attributes' => ['required' => true],
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'name',
            'label' => 'Name of Employee',
            'type' => 'text',
            'attributes' => ['required' => true],
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'father_name',
            'label' => 'Father Name',
            'type' => 'text',
            'attributes' => ['required' => true],
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'nationality',
            'label' => 'Nationality',
            'type' => 'text',
            'default' => 'Indian',
            'attributes' => ['required' => true],
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'current_address',
            'label' => 'Current Address',
            'type' => 'textarea',
            'attributes' => ['required' => true, 'rows' => 3],
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'permanent_address',
            'label' => 'Permanent Address',
            'type' => 'textarea',
            'attributes' => ['rows' => 3],
            'wrapper' => ['class' => 'form-group col-md-6'],
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
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'age',
            'label' => 'Age (Auto-calculated)',
            'type' => 'number',
            'attributes' => ['readonly' => true],
            'wrapper' => ['class' => 'form-group col-md-6'],
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
            'wrapper' => ['class' => 'form-group col-md-6'],
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
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'email',
            'label' => 'Email',
            'type' => 'email',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'phone',
            'label' => 'Contact No',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'photo_path',
            'label' => 'Photo Upload',
            'type' => 'upload',
            'upload' => true,
            'disk' => 'public',
            'wrapper' => ['class' => 'form-group col-md-12'],
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
            'wrapper' => ['class' => 'form-group col-md-6'],
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
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'uan_no',
            'label' => 'UAN No',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'esic',
            'label' => 'ESIC',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-6'],
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

        CRUD::addField([
            'name' => 'identity_proofs_section',
            'type' => 'custom_html',
            'value' => $this->getIdentityProofsSection(),
        ]);

        // Family Members Section (Dynamic)
        CRUD::addField([
            'name' => 'family_members_separator',
            'type' => 'custom_html',
            'value' => '<h4 class="mt-4 mb-3 text-primary">👨‍👩‍👧‍👦 Family Members</h4><hr>',
        ]);

        CRUD::addField([
            'name' => 'family_members_section',
            'type' => 'custom_html',
            'value' => $this->getFamilyMembersSection(),
        ]);

        // Acquaintances Section (Dynamic)
        CRUD::addField([
            'name' => 'acquaintances_separator',
            'type' => 'custom_html',
            'value' => '<h4 class="mt-4 mb-3 text-primary">🤝 Emergency Contacts / Acquaintances</h4><hr>',
        ]);

        CRUD::addField([
            'name' => 'acquaintances_section',
            'type' => 'custom_html',
            'value' => $this->getAcquaintancesSection(),
        ]);

        // Uniform Allocations Section (Dynamic)
        CRUD::addField([
            'name' => 'uniform_allocations_separator',
            'type' => 'custom_html',
            'value' => '<h4 class="mt-4 mb-3 text-primary">👕 Uniform Allocations</h4><hr>',
        ]);

        CRUD::addField([
            'name' => 'uniform_allocations_section',
            'type' => 'custom_html',
            'value' => $this->getUniformAllocationsSection(),
        ]);

        // Add JavaScript for form interactions
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
     * Generate Identity Proofs dynamic section
     */
    private function getIdentityProofsSection()
    {
        return '
            <div id="identity-proofs-container">
                <div class="identity-proof-item border rounded p-3 mb-3" data-index="0">
                    <div class="row">
                        <div class="col-md-4">
                            <label>Document Type</label>
                            <select name="identity_proofs[0][identity_proof_type]" class="form-control" required>
                                <option value="">Select Document</option>
                                <option value="aadhar_card">Aadhar Card</option>
                                <option value="pan_card">PAN Card</option>
                                <option value="voter_id">Voter ID</option>
                                <option value="driving_license">Driving License</option>
                                <option value="passport">Passport</option>
                                <option value="bank_passbook">Bank Passbook</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Document Number</label>
                            <input type="text" name="identity_proofs[0][identity_proof_no]" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label>Upload Document</label>
                            <input type="file" name="identity_proofs[0][image_file]" class="form-control" accept="image/*,application/pdf">
                        </div>
                        <div class="col-md-1">
                            <label>&nbsp;</label><br>
                            <button type="button" class="btn btn-danger btn-sm remove-identity-proof">×</button>
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" id="add-identity-proof" class="btn btn-success btn-sm mb-3">
                <i class="la la-plus"></i> Add Another Document
            </button>
        ';
    }

    /**
     * Generate Family Members dynamic section
     */
    private function getFamilyMembersSection()
    {
        return '
            <div id="family-members-container">
                <div class="family-member-item border rounded p-3 mb-3" data-index="0">
                    <div class="row">
                        <div class="col-md-3">
                            <label>Name</label>
                            <input type="text" name="family_members[0][name]" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label>Relationship</label>
                            <select name="family_members[0][relationship]" class="form-control" required>
                                <option value="">Select</option>
                                <option value="father">Father</option>
                                <option value="mother">Mother</option>
                                <option value="spouse">Spouse</option>
                                <option value="son">Son</option>
                                <option value="daughter">Daughter</option>
                                <option value="brother">Brother</option>
                                <option value="sister">Sister</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Age</label>
                            <input type="number" name="family_members[0][age]" class="form-control" min="0" max="120">
                        </div>
                        <div class="col-md-2">
                            <label>Occupation</label>
                            <input type="text" name="family_members[0][occupation]" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label>Phone</label>
                            <input type="text" name="family_members[0][phone_no]" class="form-control">
                        </div>
                        <div class="col-md-1">
                            <label>&nbsp;</label><br>
                            <button type="button" class="btn btn-danger btn-sm remove-family-member">×</button>
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" id="add-family-member" class="btn btn-success btn-sm mb-3">
                <i class="la la-plus"></i> Add Family Member
            </button>
        ';
    }

    /**
     * Generate Acquaintances dynamic section
     */
    private function getAcquaintancesSection()
    {
        return '
            <div id="acquaintances-container">
                <div class="acquaintance-item border rounded p-3 mb-3" data-index="0">
                    <div class="row">
                        <div class="col-md-3">
                            <label>Name</label>
                            <input type="text" name="acquaintances[0][name]" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label>Relationship</label>
                            <select name="acquaintances[0][relationship]" class="form-control" required>
                                <option value="">Select</option>
                                <option value="emergency_contact">Emergency Contact</option>
                                <option value="reference">Reference</option>
                                <option value="friend">Friend</option>
                                <option value="neighbor">Neighbor</option>
                                <option value="relative">Relative</option>
                                <option value="colleague">Colleague</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Phone</label>
                            <input type="text" name="acquaintances[0][phone]" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label>Address</label>
                            <textarea name="acquaintances[0][address]" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-1">
                            <label>&nbsp;</label><br>
                            <button type="button" class="btn btn-danger btn-sm remove-acquaintance">×</button>
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" id="add-acquaintance" class="btn btn-success btn-sm mb-3">
                <i class="la la-plus"></i> Add Contact
            </button>
        ';
    }

    /**
     * Generate Uniform Allocations dynamic section
     */
    private function getUniformAllocationsSection()
    {
        $clients = \App\Models\Client::select('id', 'name')->get();
        $clientOptions = '';
        foreach ($clients as $client) {
            $clientOptions .= '<option value="' . $client->id . '">' . htmlspecialchars($client->name) . '</option>';
        }

        return '
            <div id="uniform-allocations-container">
                <div class="uniform-allocation-item border rounded p-3 mb-3" data-index="0">
                    <div class="row">
                        <div class="col-md-3">
                            <label>Client</label>
                            <select name="uniform_allocations[0][client_id]" class="form-control client-select" required>
                                <option value="">Select Client</option>
                                ' . $clientOptions . '
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Uniform Type</label>
                            <select name="uniform_allocations[0][uniform_type]" class="form-control" required>
                                <option value="">Select Type</option>
                                <option value="shirt">Shirt</option>
                                <option value="pant">Pant</option>
                                <option value="belt">Belt</option>
                                <option value="cap">Cap</option>
                                <option value="shoes">Shoes</option>
                                <option value="tie">Tie</option>
                                <option value="jacket">Jacket</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Size</label>
                            <select name="uniform_allocations[0][size]" class="form-control" required>
                                <option value="">Select Size</option>
                                <option value="XS">XS</option>
                                <option value="S">S</option>
                                <option value="M">M</option>
                                <option value="L">L</option>
                                <option value="XL">XL</option>
                                <option value="XXL">XXL</option>
                                <option value="XXXL">XXXL</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Quantity</label>
                            <input type="number" name="uniform_allocations[0][quantity]" class="form-control" min="1" value="1" required>
                        </div>
                        <div class="col-md-2">
                            <label>Issue Date</label>
                            <input type="date" name="uniform_allocations[0][issue_date]" class="form-control" value="' . date('Y-m-d') . '" required>
                        </div>
                        <div class="col-md-1">
                            <label>&nbsp;</label><br>
                            <button type="button" class="btn btn-danger btn-sm remove-uniform-allocation">×</button>
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" id="add-uniform-allocation" class="btn btn-success btn-sm mb-3">
                <i class="la la-plus"></i> Add Uniform
            </button>
        ';
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
                document.addEventListener("DOMContentLoaded", function() {
                    // Auto-fill permanent address checkbox
                    const sameAddressCheckbox = document.querySelector("input[name=\"same_address\"]");
                    const currentAddress = document.querySelector("textarea[name=\"current_address\"]");
                    const permanentAddress = document.querySelector("textarea[name=\"permanent_address\"]");
                    
                    if (sameAddressCheckbox && currentAddress && permanentAddress) {
                        sameAddressCheckbox.addEventListener("change", function() {
                            if (this.checked) {
                                permanentAddress.value = currentAddress.value;
                                permanentAddress.readOnly = true;
                            } else {
                                permanentAddress.readOnly = false;
                            }
                        });
                        
                        currentAddress.addEventListener("input", function() {
                            if (sameAddressCheckbox.checked) {
                                permanentAddress.value = this.value;
                            }
                        });
                    }
                    
                    // Auto-calculate age from date of birth
                    const dobField = document.querySelector("input[name=\"date_of_birth\"]");
                    const ageField = document.querySelector("input[name=\"age\"]");
                    
                    if (dobField && ageField) {
                        dobField.addEventListener("change", function() {
                            if (this.value) {
                                const dob = new Date(this.value);
                                const today = new Date();
                                let age = today.getFullYear() - dob.getFullYear();
                                const monthDiff = today.getMonth() - dob.getMonth();
                                
                                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
                                    age--;
                                }
                                
                                ageField.value = age;
                            }
                        });
                    }

                    // Dynamic Identity Proofs functionality
                    let identityProofIndex = 1;
                    
                    document.getElementById("add-identity-proof").addEventListener("click", function() {
                        const container = document.getElementById("identity-proofs-container");
                        const newItem = document.querySelector(".identity-proof-item").cloneNode(true);
                        
                        // Update indices and clear values
                        newItem.setAttribute("data-index", identityProofIndex);
                        const inputs = newItem.querySelectorAll("input, select");
                        inputs.forEach(input => {
                            const name = input.getAttribute("name");
                            if (name) {
                                input.setAttribute("name", name.replace("[0]", `[${identityProofIndex}]`));
                                if (input.type !== "file") {
                                    input.value = "";
                                }
                            }
                        });
                        
                        container.appendChild(newItem);
                        identityProofIndex++;
                        updateRemoveButtons();
                    });

                    // Dynamic Family Members functionality
                    let familyMemberIndex = 1;
                    
                    document.getElementById("add-family-member").addEventListener("click", function() {
                        const container = document.getElementById("family-members-container");
                        const newItem = document.querySelector(".family-member-item").cloneNode(true);
                        
                        // Update indices and clear values
                        newItem.setAttribute("data-index", familyMemberIndex);
                        const inputs = newItem.querySelectorAll("input, select");
                        inputs.forEach(input => {
                            const name = input.getAttribute("name");
                            if (name) {
                                input.setAttribute("name", name.replace("[0]", `[${familyMemberIndex}]`));
                                input.value = "";
                            }
                        });
                        
                        container.appendChild(newItem);
                        familyMemberIndex++;
                        updateRemoveButtons();
                    });

                    // Dynamic Acquaintances functionality
                    let acquaintanceIndex = 1;
                    
                    document.getElementById("add-acquaintance").addEventListener("click", function() {
                        const container = document.getElementById("acquaintances-container");
                        const newItem = document.querySelector(".acquaintance-item").cloneNode(true);
                        
                        // Update indices and clear values
                        newItem.setAttribute("data-index", acquaintanceIndex);
                        const inputs = newItem.querySelectorAll("input, select, textarea");
                        inputs.forEach(input => {
                            const name = input.getAttribute("name");
                            if (name) {
                                input.setAttribute("name", name.replace("[0]", `[${acquaintanceIndex}]`));
                                input.value = "";
                            }
                        });
                        
                        container.appendChild(newItem);
                        acquaintanceIndex++;
                        updateRemoveButtons();
                    });

                    // Dynamic Uniform Allocations functionality
                    let uniformAllocationIndex = 1;
                    
                    document.getElementById("add-uniform-allocation").addEventListener("click", function() {
                        const container = document.getElementById("uniform-allocations-container");
                        const newItem = document.querySelector(".uniform-allocation-item").cloneNode(true);
                        
                        // Update indices and clear values
                        newItem.setAttribute("data-index", uniformAllocationIndex);
                        const inputs = newItem.querySelectorAll("input, select");
                        inputs.forEach(input => {
                            const name = input.getAttribute("name");
                            if (name) {
                                input.setAttribute("name", name.replace("[0]", `[${uniformAllocationIndex}]`));
                                if (input.type === "date") {
                                    input.value = new Date().toISOString().split("T")[0];
                                } else if (input.type === "number" && name.includes("quantity")) {
                                    input.value = "1";
                                } else {
                                    input.value = "";
                                }
                            }
                        });
                        
                        container.appendChild(newItem);
                        uniformAllocationIndex++;
                        updateRemoveButtons();
                    });

                    // Remove button functionality
                    function updateRemoveButtons() {
                        // Identity Proofs remove buttons
                        document.querySelectorAll(".remove-identity-proof").forEach(button => {
                            button.addEventListener("click", function() {
                                const container = document.getElementById("identity-proofs-container");
                                if (container.children.length > 1) {
                                    this.closest(".identity-proof-item").remove();
                                }
                            });
                        });

                        // Family Members remove buttons
                        document.querySelectorAll(".remove-family-member").forEach(button => {
                            button.addEventListener("click", function() {
                                const container = document.getElementById("family-members-container");
                                if (container.children.length > 1) {
                                    this.closest(".family-member-item").remove();
                                }
                            });
                        });

                        // Acquaintances remove buttons
                        document.querySelectorAll(".remove-acquaintance").forEach(button => {
                            button.addEventListener("click", function() {
                                const container = document.getElementById("acquaintances-container");
                                if (container.children.length > 1) {
                                    this.closest(".acquaintance-item").remove();
                                }
                            });
                        });

                        // Uniform Allocations remove buttons
                        document.querySelectorAll(".remove-uniform-allocation").forEach(button => {
                            button.addEventListener("click", function() {
                                const container = document.getElementById("uniform-allocations-container");
                                if (container.children.length > 1) {
                                    this.closest(".uniform-allocation-item").remove();
                                }
                            });
                        });
                    }

                    // Initialize remove buttons
                    updateRemoveButtons();
                });
                </script>
                <style>
                .identity-proof-item, .family-member-item, .acquaintance-item, .uniform-allocation-item {
                    background-color: #f8f9fa;
                    transition: all 0.3s ease;
                }
                .identity-proof-item:hover, .family-member-item:hover, .acquaintance-item:hover, .uniform-allocation-item:hover {
                    background-color: #e9ecef;
                }
                .btn-danger.btn-sm {
                    padding: 0.2rem 0.5rem;
                    font-size: 1.2rem;
                    line-height: 1;
                }
                </style>
            ',
        ]);
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
     * Store a newly created resource in storage.
     */
    public function store()
    {
        $this->crud->hasAccessOrFail('create');

        // Execute the FormRequest authorization and validation logic
        $request = $this->crud->validateRequest();

        // Store main employee record
        $item = $this->crud->create($this->crud->getStrippedSaveRequest($request));
        $this->data['entry'] = $this->crud->entry = $item;

        // Store dynamic related data
        $this->storeDynamicData($item, $request);

        // Show a success message
        \Alert::success(trans('backpack::crud.insert_success'))->flash();

        // Save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update()
    {
        $this->crud->hasAccessOrFail('update');

        // Execute the FormRequest authorization and validation logic
        $request = $this->crud->validateRequest();

        // Update main employee record
        $item = $this->crud->update($request->get($this->crud->model->getKeyName()),
                            $this->crud->getStrippedSaveRequest($request));
        $this->data['entry'] = $this->crud->entry = $item;

        // Update dynamic related data
        $this->storeDynamicData($item, $request);

        // Show a success message
        \Alert::success(trans('backpack::crud.update_success'))->flash();

        // Save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }

    /**
     * Store dynamic related data for employee
     */
    private function storeDynamicData($employee, $request)
    {
        // Store Identity Proofs
        if ($request->has('identity_proofs') && is_array($request->identity_proofs)) {
            // Clear existing records
            $employee->identityProofs()->delete();

            foreach ($request->identity_proofs as $index => $proofData) {
                if (!empty($proofData['identity_proof_type']) && !empty($proofData['identity_proof_no'])) {
                    $proof = new \App\Models\EmployeeIdentityProof([
                        'identity_proof_type' => $proofData['identity_proof_type'],
                        'identity_proof_no' => $proofData['identity_proof_no'],
                    ]);

                    // Handle file upload
                    if (isset($proofData['image_file']) && $proofData['image_file']->isValid()) {
                        $file = $proofData['image_file'];
                        $path = $file->store('identity_proofs', 'public');
                        $proof->image_path = $path;
                    }

                    $employee->identityProofs()->save($proof);
                }
            }
        }

        // Store Family Members
        if ($request->has('family_members') && is_array($request->family_members)) {
            // Clear existing records
            $employee->familyMembers()->delete();

            foreach ($request->family_members as $memberData) {
                if (!empty($memberData['name']) && !empty($memberData['relationship'])) {
                    $member = new \App\Models\EmployeeFamilyMember([
                        'name' => $memberData['name'],
                        'relationship' => $memberData['relationship'],
                        'age' => $memberData['age'] ?? null,
                        'phone_no' => $memberData['phone_no'] ?? null,
                        'is_nominee' => false, // Default value
                    ]);

                    $employee->familyMembers()->save($member);
                }
            }
        }

        // Store Acquaintances
        if ($request->has('acquaintances') && is_array($request->acquaintances)) {
            // Clear existing records
            $employee->acquaintances()->delete();

            foreach ($request->acquaintances as $acquaintanceData) {
                if (!empty($acquaintanceData['name']) && !empty($acquaintanceData['relationship'])) {
                    $acquaintance = new \App\Models\EmployeeAcquaintance([
                        'name' => $acquaintanceData['name'],
                        'relationship' => $acquaintanceData['relationship'],
                        'phone' => $acquaintanceData['phone'],
                        'address' => $acquaintanceData['address'] ?? null,
                    ]);

                    $employee->acquaintances()->save($acquaintance);
                }
            }
        }

        // Store Uniform Allocations
        if ($request->has('uniform_allocations') && is_array($request->uniform_allocations)) {
            // Clear existing records
            $employee->uniformAllocations()->delete();

            foreach ($request->uniform_allocations as $allocationData) {
                if (!empty($allocationData['client_id']) && !empty($allocationData['uniform_type'])) {
                    $allocation = new \App\Models\EmployeeUniformAllocation([
                        'client_id' => $allocationData['client_id'],
                        'uniform_type' => $allocationData['uniform_type'],
                        'size' => $allocationData['size'],
                        'quantity' => $allocationData['quantity'],
                        'issue_date' => $allocationData['issue_date'],
                    ]);

                    $employee->uniformAllocations()->save($allocation);
                }
            }
        }
    }
}
