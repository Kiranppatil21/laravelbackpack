<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id', 'device_name', 'device_type', 'location', 'ip_address', 'mac_address',
        'status', 'capabilities', 'configuration', 'last_heartbeat', 'notes', 'managed_by'
    ];

    protected $casts = [
        'capabilities' => 'array',
        'configuration' => 'array',
        'last_heartbeat' => 'datetime',
    ];

    // Relationships
    public function managedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'managed_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOnline($query, int $minutesThreshold = 5)
    {
        return $query->where('last_heartbeat', '>=', now()->subMinutes($minutesThreshold));
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('device_type', $type);
    }

    public function scopeByLocation($query, string $location)
    {
        return $query->where('location', 'like', "%{$location}%");
    }

    // Helper methods
    public function isOnline(int $minutesThreshold = 5): bool
    {
        return $this->last_heartbeat && 
               $this->last_heartbeat >= now()->subMinutes($minutesThreshold);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasCapability(string $capability): bool
    {
        return is_array($this->capabilities) && 
               in_array($capability, $this->capabilities);
    }

    public function updateHeartbeat(): void
    {
        $this->update(['last_heartbeat' => now()]);
    }

    public function markAsOffline(): void
    {
        $this->update(['status' => 'inactive']);
    }

    public function markAsError(string $error = null): void
    {
        $notes = $error ? $this->notes . "\nError: " . $error : $this->notes;
        $this->update([
            'status' => 'error',
            'notes' => $notes,
        ]);
    }

    public function getConfigValue(string $key, $default = null)
    {
        return $this->configuration[$key] ?? $default;
    }

    public function setConfigValue(string $key, $value): void
    {
        $config = $this->configuration ?? [];
        $config[$key] = $value;
        $this->update(['configuration' => $config]);
    }
}