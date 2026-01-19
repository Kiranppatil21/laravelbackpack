<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class EmployeeAttendanceDetail extends Model
{
    use HasFactory;

    protected $table = 'employee_attendance_details';

    protected $fillable = [
        'attendance_master_id',
        'employee_id',
        'site_id',
        'date',
        'shift',
        'is_present',
        'is_ot'
    ];

    protected $casts = [
        'attendance_master_id' => 'integer',
        'employee_id' => 'integer',
        'site_id' => 'integer',
        'date' => 'date',
        'is_present' => 'boolean',
        'is_ot' => 'boolean',
    ];

    /**
     * Get the attendance master record
     */
    public function master(): BelongsTo
    {
        return $this->belongsTo(EmployeeAttendanceMaster::class, 'attendance_master_id');
    }

    /**
     * Get the employee for this attendance record
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Get the client (site) for this attendance record
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'site_id');
    }

    /**
     * Get formatted date
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->date->format('d-m-Y');
    }

    /**
     * Get shift name
     */
    public function getShiftNameAttribute(): string
    {
        return match($this->shift) {
            '1' => 'First Shift',
            '2' => 'Second Shift', 
            '3' => 'Third Shift',
            default => 'Unknown Shift'
        };
    }

    /**
     * Scope for filtering by month
     */
    public function scopeByMonth($query, string $month)
    {
        return $query->whereYear('date', Carbon::createFromFormat('Y-m', $month)->year)
                    ->whereMonth('date', Carbon::createFromFormat('Y-m', $month)->month);
    }

    /**
     * Scope for filtering by employee
     */
    public function scopeByEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * Scope for filtering by site
     */
    public function scopeBySite($query, int $siteId)
    {
        return $query->where('site_id', $siteId);
    }
}
