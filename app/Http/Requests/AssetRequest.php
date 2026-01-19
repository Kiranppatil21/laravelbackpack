<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'asset_name' => 'required|string|max:255',
            'asset_code' => 'required|string|max:255|unique:assets,asset_code,' . $this->id,
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255|unique:assets,serial_number,' . $this->id,
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'vendor_name' => 'nullable|string|max:255',
            'vendor_contact' => 'nullable|string|max:255',
            'current_value' => 'nullable|numeric|min:0',
            'status' => 'required|in:available,assigned,maintenance,retired,lost',
            'condition' => 'nullable|in:excellent,good,fair,poor',
            'location' => 'nullable|string|max:255',
            'assigned_to_employee_id' => 'nullable|exists:employees,id',
            'assigned_to_client_id' => 'nullable|exists:clients,id',
            'assigned_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'next_maintenance_date' => 'nullable|date',
            'maintenance_notes' => 'nullable|string',
            'image_path' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'asset_name' => 'asset name',
            'asset_code' => 'asset code',
            'assigned_to_employee_id' => 'assigned employee',
            'assigned_to_client_id' => 'assigned client',
        ];
    }
}
