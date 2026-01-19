<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use \App\Models\Concerns\BelongsToTenant;
    use CrudTrait;

    protected $fillable = [
        'tenant_uuid',
        'shift_name',
        'shift_code',
        'start_time',
        'end_time',
        'duration_hours',
        'ot_after_hours',
        'is_night_shift',
        'night_allowance',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_night_shift' => 'boolean',
        'is_active' => 'boolean',
        'night_allowance' => 'decimal:2',
        'ot_after_hours' => 'decimal:2'
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(ShiftAssignment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    protected static function booted()
    {
        static::creating(function ($shift) {
            if (empty($shift->tenant_uuid)) {
                if (function_exists('tenant') && tenant()) {
                    $shift->tenant_uuid = tenant()->id;
                } elseif (backpack_user() && backpack_user()->tenant_id) {
                    $shift->tenant_uuid = backpack_user()->tenant_id;
                } else {
                    $shift->tenant_uuid = 'default-uuid';
                }
            }
        });
    }
}
