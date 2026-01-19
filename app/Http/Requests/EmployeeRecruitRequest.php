<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeRecruitRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() !== null;
    }

    public function rules()
    {
        return [
            // support either a single 'name' or first_name/last_name pair
            'name' => 'nullable|string|max:191',
            'first_name' => 'required_without:name|string|max:191',
            'last_name' => 'nullable|string|max:191',
            'email' => 'nullable|email|max:191',
            'phone' => 'nullable|string|max:50',
            'client_id' => 'nullable|exists:clients,id',
            'job_role' => 'nullable|string|max:191',
            'shift' => 'nullable|array',
            'shift.*' => 'nullable|string',
            'aadhar' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'pan' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'police_verification' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];
    }
}
