<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyJobOpeningRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Only allow logged in users
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
            'title' => 'required|string|max:255',
            'department' => 'required|string|max:100',
            'location' => 'required|string|max:255',
            'type' => 'required|in:full-time,part-time,contract,internship',
            'experience_level' => 'nullable|string|max:100',
            'description' => 'required|string',
            'requirements' => 'nullable|string',
            'salary_range' => 'nullable|string|max:100',
            'contact_email' => 'required|email|max:255',
            'status' => 'required|in:active,inactive,closed',
            'priority' => 'required|integer|min:1|max:3',
            'application_deadline' => 'nullable|date|after:today',
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
            'title' => 'job title',
            'department' => 'department',
            'location' => 'location',
            'type' => 'job type',
            'experience_level' => 'experience level',
            'description' => 'job description',
            'requirements' => 'requirements',
            'salary_range' => 'salary range',
            'contact_email' => 'contact email',
            'status' => 'status',
            'priority' => 'priority',
            'application_deadline' => 'application deadline',
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
            'title.required' => 'The job title is required.',
            'title.max' => 'The job title may not be greater than 255 characters.',
            'department.required' => 'The department is required.',
            'location.required' => 'The location is required.',
            'type.required' => 'The job type is required.',
            'type.in' => 'The selected job type is invalid.',
            'description.required' => 'The job description is required.',
            'contact_email.required' => 'The contact email is required.',
            'contact_email.email' => 'The contact email must be a valid email address.',
            'status.required' => 'The status is required.',
            'status.in' => 'The selected status is invalid.',
            'priority.required' => 'The priority is required.',
            'priority.integer' => 'The priority must be a number.',
            'priority.min' => 'The priority must be at least 1.',
            'priority.max' => 'The priority may not be greater than 3.',
            'application_deadline.after' => 'The application deadline must be a future date.',
        ];
    }
}