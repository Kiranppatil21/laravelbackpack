<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Training extends Model
{
    use \App\Models\Concerns\BelongsToTenant;
    use CrudTrait;

    protected $fillable = [
        'tenant_uuid',
        'training_name',
        'training_code',
        'category',
        'description',
        'trainer_name',
        'trainer_contact',
        'start_date',
        'end_date',
        'duration_hours',
        'venue',
        'max_participants',
        'cost_per_participant',
        'status',
        'certificate_template',
        'is_mandatory',
        'validity_months',
        'materials_provided'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_mandatory' => 'boolean',
        'cost_per_participant' => 'decimal:2'
    ];

    public function participants(): HasMany
    {
        return $this->hasMany(TrainingParticipant::class);
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'training_participants')
            ->withPivot(['attendance_status', 'score', 'grade', 'certificate_issued', 'certificate_number', 'rating'])
            ->withTimestamps();
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now())->where('status', 'scheduled');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    protected static function booted()
    {
        static::creating(function ($training) {
            if (empty($training->tenant_uuid)) {
                if (function_exists('tenant') && tenant()) {
                    $training->tenant_uuid = tenant()->id;
                } elseif (backpack_user() && backpack_user()->tenant_id) {
                    $training->tenant_uuid = backpack_user()->tenant_id;
                } else {
                    $training->tenant_uuid = 'default-uuid';
                }
            }
        });
    }
}
