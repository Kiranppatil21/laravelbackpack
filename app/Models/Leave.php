<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Leave extends Model
{
    use \App\Models\Concerns\BelongsToTenant;
    use CrudTrait;

    protected $fillable = [
        'tenant_uuid',
        'employee_id',
        'leave_type',
        'start_date',
        'end_date',
        'days',
        'reason',
        'status',
        'approved_by',
        'approver_remarks',
        'approved_at',
        'supporting_document',
        'is_half_day',
        'half_day_period'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'days' => 'decimal:2',
        'approved_at' => 'datetime',
        'is_half_day' => 'boolean'
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getLeaveStatusAttribute()
    {
        return ucfirst($this->status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    protected static function booted()
    {
        static::creating(function ($leave) {
            if (empty($leave->tenant_uuid)) {
                if (function_exists('tenant') && tenant()) {
                    $leave->tenant_uuid = tenant()->id;
                } elseif (backpack_user() && backpack_user()->tenant_id) {
                    $leave->tenant_uuid = backpack_user()->tenant_id;
                } else {
                    $leave->tenant_uuid = 'default-uuid';
                }
            }
        });
    }
}
