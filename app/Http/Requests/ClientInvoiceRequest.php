<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientInvoiceRequest extends FormRequest
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
            'client_id' => 'required|exists:clients,id',
            'month' => 'required|string|max:20',
            'bill_date' => 'required|date',
            'invoice_amount' => 'nullable|numeric|min:0',
            'other_amount_with_tax' => 'nullable|numeric|min:0',
            'other_amount_without_tax' => 'nullable|numeric|min:0',
            'service_charge_percent' => 'nullable|numeric|min:0|max:100',
            'service_charge_amount' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'cst_amount' => 'nullable|numeric|min:0',
            'gross_bill_amount' => 'nullable|numeric|min:0',
            'grand_total' => 'nullable|numeric|min:0',
            'comments' => 'nullable|string|max:1000',
            'monthly_comment' => 'nullable|string|max:1000',
            'send_mail' => 'nullable|boolean',
            
            // Employee validation
            'employees' => 'nullable|array',
            'employees.*.employee_id' => 'required_with:employees|exists:employees,id',
            'employees.*.duty_days' => 'nullable|numeric|min:0',
            'employees.*.overtime_hours' => 'nullable|numeric|min:0',
            'employees.*.daily_rate' => 'nullable|numeric|min:0',
            'employees.*.overtime_rate' => 'nullable|numeric|min:0',
            'employees.*.payment' => 'nullable|numeric|min:0',
            'employees.*.overtime_payment' => 'nullable|numeric|min:0',
            'employees.*.total_payment' => 'nullable|numeric|min:0',
            
            // Additional charges validation
            'additional_charges' => 'nullable|array',
            'additional_charges.*.date' => 'required_with:additional_charges|date',
            'additional_charges.*.amount' => 'required_with:additional_charges|numeric|min:0',
            'additional_charges.*.comment' => 'nullable|string|max:500',
            
            // Tax validation
            'taxes' => 'nullable|array',
            'taxes.*.tax_type' => 'required_with:taxes|in:SGST,CGST,IGST,VAT,CST',
            'taxes.*.tax_percent' => 'required_with:taxes|numeric|min:0|max:100',
            'taxes.*.tax_amount' => 'nullable|numeric|min:0',
            'taxes.*.tax_no' => 'nullable|string|max:50',
            
            // Service tax details validation
            'service_tax_details' => 'nullable|array',
            'service_tax_details.*.amount' => 'required_with:service_tax_details|numeric|min:0',
            'service_tax_details.*.service_type' => 'nullable|string|max:100',
            'service_tax_details.*.tax_type' => 'nullable|string|max:50',
            'service_tax_details.*.tax_percent' => 'nullable|numeric|min:0|max:100',
            'service_tax_details.*.final_amount' => 'nullable|numeric|min:0',
            'service_tax_details.*.comment' => 'nullable|string|max:500',
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
            //
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
