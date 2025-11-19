<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAttendanceMaster extends Model
{
    use HasFactory;

    protected $table = 'employee_attendance_master';

    protected $fillable = [
        'tenant_id',
        'site_id',
        'month',
        'user_type',
        'created_by'
    ];

    protected $casts = [
        'site_id' => 'integer',
        'created_by' => 'integer',
    ];

    /**
     * Get the client (site) that owns this attendance record
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'site_id');
    }

    /**
     * Get the user who created this attendance record
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the attendance details for this master record
     */
    public function details(): HasMany
    {
        return $this->hasMany(EmployeeAttendanceDetail::class, 'attendance_master_id');
    }

    /**
     * Get formatted month name
     */
    public function getFormattedMonthAttribute(): string
    {
        return \Carbon\Carbon::createFromFormat('Y-m', $this->month)->format('F Y');
    }

    /**
     * Get total employees in this attendance record
     */
    public function getTotalEmployeesAttribute(): int
    {
        return $this->details()->distinct('employee_id')->count();
    }

    /**
     * Get total working days in this attendance record
     */
    public function getTotalWorkingDaysAttribute(): int
    {
        return $this->details()->where('is_present', true)->count();
    }
}
