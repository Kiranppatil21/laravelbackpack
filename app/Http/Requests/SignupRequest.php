<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SignupRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|unique:domains,domain',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255',
            'price_id' => 'nullable|string|max:255',
            'gateway' => 'nullable|in:stripe,razorpay',
            'amount' => 'nullable|numeric|min:0',
        ];
    }
}
