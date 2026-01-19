<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftAssignment extends Model
{
    use \App\Models\Concerns\BelongsToTenant;
    use CrudTrait;

    protected $fillable = [
        'tenant_uuid',
        'shift_id',
        'employee_id',
        'client_id',
        'assignment_date',
        'status',
        'actual_start_time',
        'actual_end_time',
        'notes'
    ];

    protected $casts = [
        'assignment_date' => 'date'
    ];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    protected static function booted()
    {
        static::creating(function ($assignment) {
            if (empty($assignment->tenant_uuid)) {
                if (function_exists('tenant') && tenant()) {
                    $assignment->tenant_uuid = tenant()->id;
                } elseif (backpack_user() && backpack_user()->tenant_id) {
                    $assignment->tenant_uuid = backpack_user()->tenant_id;
                } else {
                    $assignment->tenant_uuid = 'default-uuid';
                }
            }
        });
    }
}
