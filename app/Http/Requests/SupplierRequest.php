<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return backpack_auth()->check();
    }

    public function rules(): array
    {
        return [
            'supplier_code' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'email' => 'nullable|email',
            'address' => 'required|string',
            'status' => 'required|in:active,inactive,blacklisted',
        ];
    }
}
