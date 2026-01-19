<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContractRequest extends FormRequest
{
    public function authorize()
    {
        return backpack_auth()->check();
    }

    public function rules()
    {
        return [
            'contract_number' => 'required|string|max:50|unique:contracts,contract_number,' . $this->id,
            'client_id' => 'required|exists:clients,id',
            'agency_id' => 'required|exists:agencies,id',
            'contract_type' => 'required|in:security-services,manpower,facility-management,event-security',
            'service_type' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'duration_months' => 'required|integer|min:1|max:120',
            'number_of_guards' => 'required|integer|min:1|max:10000',
            'shift_pattern' => 'required|in:12-hour,8-hour,24-hour',
            'monthly_contract_value' => 'required|numeric|min:0',
            'per_guard_rate' => 'required|numeric|min:0',
            'overtime_rate' => 'nullable|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,quarterly,annual',
            'payment_terms_days' => 'required|integer|min:0|max:180',
            'status' => 'sometimes|in:draft,active,expired,renewed,cancelled,terminated',
            'scope_of_work' => 'required|string|max:5000',
            'terms_and_conditions' => 'nullable|string|max:10000',
            'special_instructions' => 'nullable|string|max:2000',
            'contract_document' => 'nullable|file|mimes:pdf,doc,docx|max:20480',
            'signed_contract' => 'nullable|file|mimes:pdf|max:20480',
            'signed_date' => 'nullable|date',
            'client_signatory' => 'nullable|string|max:255',
            'agency_signatory' => 'nullable|string|max:255',
            'auto_renewal' => 'boolean',
            'renewal_notice_days' => 'nullable|integer|min:1|max:365',
            'security_deposit' => 'nullable|numeric|min:0',
            'deposit_refunded' => 'boolean'
        ];
    }

    public function messages()
    {
        return [
            'contract_number.unique' => 'This contract number is already in use',
            'end_date.after' => 'End date must be after start date',
            'contract_document.max' => 'Document size must not exceed 20MB',
            'signed_contract.max' => 'Signed contract size must not exceed 20MB'
        ];
    }
}
