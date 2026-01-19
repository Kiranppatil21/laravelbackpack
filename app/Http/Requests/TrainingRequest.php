<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrainingRequest extends FormRequest
{
    public function authorize()
    {
        return backpack_auth()->check();
    }

    public function rules()
    {
        return [
            'training_name' => 'required|string|max:255',
            'training_code' => 'required|string|max:50|unique:trainings,training_code,' . $this->id,
            'category' => 'required|in:security,safety,first-aid,fire-fighting,customer-service,technical',
            'description' => 'required|string|max:2000',
            'trainer_name' => 'nullable|string|max:255',
            'trainer_contact' => 'nullable|string|max:15',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'duration_hours' => 'required|integer|min:1|max:1000',
            'venue' => 'required|string|max:500',
            'max_participants' => 'nullable|integer|min:1|max:500',
            'cost_per_participant' => 'nullable|numeric|min:0',
            'status' => 'sometimes|in:scheduled,ongoing,completed,cancelled',
            'certificate_template' => 'nullable|string|max:500',
            'is_mandatory' => 'nullable|boolean',
            'validity_months' => 'nullable|integer|min:1|max:120',
            'materials_provided' => 'nullable|string|max:1000'
        ];
    }

    public function messages()
    {
        return [
            'training_code.unique' => 'This training code is already in use',
            'end_date.after_or_equal' => 'End date must be after or equal to start date',
            'max_participants.max' => 'Maximum participants cannot exceed 500'
        ];
    }
}
