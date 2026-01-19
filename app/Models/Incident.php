<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incident extends Model
{
    use \App\Models\Concerns\BelongsToTenant;
    use CrudTrait;

    protected $fillable = [
        'tenant_uuid',
        'incident_number',
        'incident_type',
        'severity',
        'client_id',
        'reported_by_employee_id',
        'incident_datetime',
        'location',
        'description',
        'action_taken',
        'status',
        'police_notified',
        'police_report_number',
        'client_notified',
        'client_notified_at',
        'client_response',
        'witnesses',
        'involved_parties',
        'evidence_photo_1',
        'evidence_photo_2',
        'evidence_photo_3',
        'evidence_document',
        'estimated_loss',
        'insurance_claim',
        'claim_reference',
        'assigned_to',
        'investigation_notes',
        'resolution_summary',
        'resolved_at'
    ];

    protected $casts = [
        'incident_datetime' => 'datetime',
        'client_notified_at' => 'datetime',
        'resolved_at' => 'datetime',
        'police_notified' => 'boolean',
        'client_notified' => 'boolean',
        'insurance_claim' => 'boolean',
        'estimated_loss' => 'decimal:2',
        'witnesses' => 'json',
        'involved_parties' => 'json'
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reported_by_employee_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    protected static function booted()
    {
        static::creating(function ($incident) {
            if (empty($incident->tenant_uuid)) {
                if (function_exists('tenant') && tenant()) {
                    $incident->tenant_uuid = tenant()->id;
                } elseif (backpack_user() && backpack_user()->tenant_id) {
                    $incident->tenant_uuid = backpack_user()->tenant_id;
                } else {
                    $incident->tenant_uuid = 'default-uuid';
                }
            }
        });
    }
}
