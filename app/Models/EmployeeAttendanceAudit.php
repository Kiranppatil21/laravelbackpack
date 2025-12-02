<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAttendanceAudit extends Model
{
    use HasFactory;

    protected $table = 'employee_attendance_audits';

    protected $fillable = [
        'attendance_master_id',
        'attendance_detail_id',
        'site_id',
        'changed_by',
        'action',
        'before',
        'after',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'attendance_master_id' => 'integer',
        'attendance_detail_id' => 'integer',
        'site_id' => 'integer',
        'changed_by' => 'integer',
        'before' => 'array',
        'after' => 'array',
    ];

    public function master(): BelongsTo
    {
        return $this->belongsTo(EmployeeAttendanceMaster::class, 'attendance_master_id');
    }

    /**
     * User who performed the change.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
