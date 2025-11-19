<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // only allow updates if the user is logged in
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // Personal Information Details
            'designation' => 'nullable|string|max:255',
            'education' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'nationality' => 'required|string|max:255',
            'current_address' => 'required|string|max:1000',
            'permanent_address' => 'nullable|string|max:1000',
            'same_address' => 'boolean',
            'date_of_birth' => 'required|date|before:today',
            'age' => 'nullable|integer|min:18|max:65',
            'gender' => 'nullable|in:Male,Female,Other',
            'marital_status' => 'nullable|in:Single,Married,Divorced,Widowed',
            'email' => 'nullable|email|max:255|unique:employees,email,' . $this->id,
            'phone' => 'nullable|string|max:15',
            'photo_path' => 'nullable|file|image|max:2048',

            // Shift Hour
            'shift_hour' => 'nullable|string|max:255',

            // PF/ESIC Details
            'pf_no' => 'nullable|string|max:255',
            'uan_no' => 'nullable|string|max:255',
            'esic' => 'nullable|string|max:255',
            'esic_percentage' => 'nullable|numeric|between:0,100',
            'pf_percentage' => 'nullable|numeric|between:0,100',
            'pt_charges_apply' => 'boolean',

            // Bank Account Details
            'bank_name' => 'required|string|max:255',
            'bank_branch' => 'nullable|string|max:255',
            'account_no' => 'required|string|max:50',
            'ifsc_code' => 'required|string|max:11',
            'bank_phone_no' => 'nullable|string|max:15',
            'account_holder_name' => 'required|string|max:255',

            // Old Company Details
            'old_company_name' => 'nullable|string|max:255',
            'old_company_year' => 'nullable|string|max:4',
            'reason_for_leaving' => 'nullable|string|max:1000',

            // Identity Proofs (Dynamic Array)
            'identity_proofs' => 'nullable|array',
            'identity_proofs.*.document_type' => 'required|string|in:aadhar_card,pan_card,voter_id,driving_license,passport,bank_passbook,other',
            'identity_proofs.*.document_number' => 'required|string|max:255',
            'identity_proofs.*.document_file' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:2048',

            // Family Members (Dynamic Array)
            'family_members' => 'nullable|array',
            'family_members.*.name' => 'required|string|max:255',
            'family_members.*.relationship' => 'required|string|in:father,mother,spouse,son,daughter,brother,sister,other',
            'family_members.*.age' => 'nullable|integer|min:0|max:120',
            'family_members.*.occupation' => 'nullable|string|max:255',
            'family_members.*.phone' => 'nullable|string|max:15',

            // Acquaintances (Dynamic Array)
            'acquaintances' => 'nullable|array',
            'acquaintances.*.name' => 'required|string|max:255',
            'acquaintances.*.relationship' => 'required|string|in:emergency_contact,reference,friend,neighbor,relative,colleague,other',
            'acquaintances.*.phone' => 'required|string|max:15',
            'acquaintances.*.address' => 'nullable|string|max:500',

            // Uniform Allocations (Dynamic Array)
            'uniform_allocations' => 'nullable|array',
            'uniform_allocations.*.client_id' => 'required|exists:clients,id',
            'uniform_allocations.*.uniform_type' => 'required|string|in:shirt,pant,belt,cap,shoes,tie,jacket,other',
            'uniform_allocations.*.size' => 'required|string|in:XS,S,M,L,XL,XXL,XXXL',
            'uniform_allocations.*.quantity' => 'required|integer|min:1|max:10',
            'uniform_allocations.*.issue_date' => 'required|date|before_or_equal:today',
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'name' => 'Employee Name',
            'father_name' => 'Father Name',
            'current_address' => 'Current Address',
            'date_of_birth' => 'Date of Birth',
            'bank_name' => 'Bank Name',
            'account_no' => 'Account Number',
            'ifsc_code' => 'IFSC Code',
            'account_holder_name' => 'Account Holder Name',
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            //
        ];
    }
}
