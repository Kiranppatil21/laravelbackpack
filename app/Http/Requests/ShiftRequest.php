<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShiftRequest extends FormRequest
{
    public function authorize()
    {
        return backpack_auth()->check();
    }

    public function rules()
    {
        return [
            'shift_name' => 'required|string|max:255',
            'shift_code' => 'required|string|max:50|unique:shifts,shift_code,' . $this->id,
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'duration_hours' => 'required|integer|min:1|max:24',
            'ot_after_hours' => 'nullable|numeric|min:0|max:24',
            'is_night_shift' => 'boolean',
            'night_allowance' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean'
        ];
    }

    public function messages()
    {
        return [
            'shift_code.unique' => 'This shift code is already in use',
            'end_time.after' => 'End time must be after start time',
            'duration_hours.max' => 'Duration cannot exceed 24 hours'
        ];
    }
}
