<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'timestamp',
        'user_id',
        'user_email',
        'user_name',
        'ip_address',
        'user_agent',
        'method',
        'url',
        'route',
        'status_code',
        'duration_ms',
        'request_size',
        'response_size',
        'request_params',
        'uploaded_files',
        'event_type',
        'notes',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'request_params' => 'array',
        'uploaded_files' => 'array',
        'duration_ms' => 'decimal:2',
    ];

    /**
     * Get the user that performed this action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for filtering by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('timestamp', [$startDate, $endDate]);
    }

    /**
     * Scope for filtering by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for filtering by IP address.
     */
    public function scopeByIp($query, $ipAddress)
    {
        return $query->where('ip_address', $ipAddress);
    }

    /**
     * Scope for filtering by route.
     */
    public function scopeByRoute($query, $route)
    {
        return $query->where('route', $route);
    }
}
