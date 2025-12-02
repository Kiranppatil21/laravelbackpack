<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientRequest extends FormRequest
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
            'name' => 'required|min:2|max:255',
            'email' => 'required|email|unique:clients,email,'.$this->id,
            // Financial / billing fields
            'billing_rate' => 'nullable|numeric|min:0',
            'salary_cost' => 'nullable|numeric|min:0',
            'esi_rate' => 'nullable|numeric|min:0|max:100',
            'pf_rate' => 'nullable|numeric|min:0|max:100',
            'licensing_cost' => 'nullable|numeric|min:0',
            'administrative_overhead' => 'nullable|numeric|min:0',
            'gross_margin' => 'nullable|numeric|min:0',
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
