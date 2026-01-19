<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingParticipant extends Model
{
    protected $fillable = [
        'training_id',
        'employee_id',
        'attendance_status',
        'score',
        'grade',
        'certificate_issued',
        'certificate_number',
        'certificate_issued_date',
        'certificate_expiry_date',
        'feedback',
        'rating'
    ];

    protected $casts = [
        'certificate_issued' => 'boolean',
        'certificate_issued_date' => 'date',
        'certificate_expiry_date' => 'date'
    ];

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
