<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TenantRequest extends FormRequest
{
    public function authorize()
    {
        return true; // protected by Backpack middleware/roles
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255',
        ];
    }
}
