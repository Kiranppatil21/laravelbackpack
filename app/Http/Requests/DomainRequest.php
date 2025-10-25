<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DomainRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'domain' => 'required|string|max:255|unique:domains,domain',
            'tenant_id' => 'required',
        ];
    }
}
