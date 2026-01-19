<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IncidentRequest extends FormRequest
{
    public function authorize()
    {
        return backpack_auth()->check();
    }

    public function rules()
    {
        return [
            'incident_number' => 'required|string|max:50|unique:incidents,incident_number,' . $this->id,
            'incident_type' => 'required|in:theft,assault,fire,medical,accident,property-damage,suspicious-activity,breach',
            'severity' => 'required|in:low,medium,high,critical',
            'client_id' => 'required|exists:clients,id',
            'reported_by_employee_id' => 'required|exists:employees,id',
            'incident_datetime' => 'required|date',
            'location' => 'required|string|max:500',
            'description' => 'required|string|max:5000',
            'action_taken' => 'required|string|max:2000',
            'status' => 'sometimes|in:open,investigating,resolved,closed',
            'police_notified' => 'boolean',
            'police_report_number' => 'nullable|string|max:100',
            'client_notified' => 'boolean',
            'client_response' => 'nullable|string|max:1000',
            'witnesses' => 'nullable|json',
            'involved_parties' => 'nullable|json',
            'evidence_photo_1' => 'nullable|file|image|max:10240',
            'evidence_photo_2' => 'nullable|file|image|max:10240',
            'evidence_photo_3' => 'nullable|file|image|max:10240',
            'evidence_document' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'estimated_loss' => 'nullable|numeric|min:0',
            'insurance_claim' => 'boolean',
            'claim_reference' => 'nullable|string|max:100',
            'assigned_to' => 'nullable|exists:users,id',
            'investigation_notes' => 'nullable|string|max:5000',
            'resolution_summary' => 'nullable|string|max:2000'
        ];
    }

    public function messages()
    {
        return [
            'incident_number.unique' => 'This incident number is already in use',
            'evidence_photo_1.max' => 'Photo size must not exceed 10MB',
            'evidence_document.max' => 'Document size must not exceed 10MB'
        ];
    }
}
