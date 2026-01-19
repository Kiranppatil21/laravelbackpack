<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LeaveRequest extends FormRequest
{
    public function authorize()
    {
        return backpack_auth()->check();
    }

    public function rules()
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'leave_type' => 'required|in:casual,sick,annual,compensatory,maternity,paternity,unpaid',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'days' => 'required|numeric|min:0.5|max:365',
            'reason' => 'required|string|max:1000',
            'status' => 'sometimes|in:pending,approved,rejected,cancelled',
            'approved_by' => 'nullable|exists:users,id',
            'approver_remarks' => 'nullable|string|max:500',
            'supporting_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'is_half_day' => 'nullable|boolean',
            'half_day_period' => 'required_if:is_half_day,1|in:morning,afternoon'
        ];
    }

    public function messages()
    {
        return [
            'employee_id.required' => 'Employee selection is required',
            'leave_type.required' => 'Leave type is required',
            'start_date.after_or_equal' => 'Start date cannot be in the past',
            'end_date.after_or_equal' => 'End date must be after or equal to start date',
            'supporting_document.max' => 'Document size must not exceed 5MB'
        ];
    }
}
