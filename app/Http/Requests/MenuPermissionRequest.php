<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MenuPermissionRequest extends FormRequest
{
    public function authorize()
    {
        return backpack_auth()->check();
    }

    public function rules()
    {
        return [
            'menu_key' => 'required|string|max:100|unique:menu_permissions,menu_key,' . $this->id,
            'menu_label' => 'required|string|max:255',
            'menu_url' => 'nullable|string|max:500',
            'parent_key' => 'nullable|string|exists:menu_permissions,menu_key',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'menu_key.required' => 'The menu key is required.',
            'menu_key.unique' => 'This menu key already exists.',
            'menu_label.required' => 'The menu label is required.',
            'parent_key.exists' => 'The selected parent menu does not exist.',
        ];
    }
}
