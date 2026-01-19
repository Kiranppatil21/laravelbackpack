<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'severity', 'title', 'description', 'visitor_id', 'visit_log_id',
        'triggered_by', 'assigned_to', 'status', 'occurred_at', 'resolved_at',
        'resolution_notes', 'metadata'
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'resolved_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Relationships
    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function visitLog(): BelongsTo
    {
        return $this->belongsTo(VisitLog::class);
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('severity', ['high', 'critical']);
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('occurred_at', '>=', now()->subHours($hours));
    }

    // Helper methods
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isCritical(): bool
    {
        return $this->severity === 'critical';
    }

    public function assign(User $user): void
    {
        $this->update(['assigned_to' => $user->id]);
    }

    public function resolve(string $notes = null, User $resolvedBy = null): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolution_notes' => $notes,
        ]);
    }

    public function markAsFalseAlarm(string $notes = null): void
    {
        $this->update([
            'status' => 'false_alarm',
            'resolved_at' => now(),
            'resolution_notes' => $notes,
        ]);
    }

    public function escalate(): void
    {
        $newSeverity = match ($this->severity) {
            'low' => 'medium',
            'medium' => 'high',
            'high' => 'critical',
            default => 'critical'
        };

        $this->update(['severity' => $newSeverity]);
    }

    public static function createWatchlistAlert(Visitor $visitor, VisitorWatchlist $watchlistEntry): self
    {
        return static::create([
            'type' => 'watchlist_entry',
            'severity' => match ($watchlistEntry->threat_level) {
                'low' => 'low',
                'medium' => 'medium',
                'high' => 'high',
                'critical' => 'critical',
            },
            'title' => 'Watchlist Visitor Detected',
            'description' => "Visitor {$visitor->name} is on the watchlist: {$watchlistEntry->description}",
            'visitor_id' => $visitor->id,
            'occurred_at' => now(),
            'metadata' => [
                'watchlist_id' => $watchlistEntry->id,
                'threat_level' => $watchlistEntry->threat_level,
                'reason' => $watchlistEntry->reason,
            ],
        ]);
    }

    public static function createOverstayAlert(VisitLog $visitLog): self
    {
        return static::create([
            'type' => 'overstay',
            'severity' => 'medium',
            'title' => 'Visitor Overstay Detected',
            'description' => "Visitor has exceeded expected checkout time",
            'visitor_id' => $visitLog->visitor_id,
            'visit_log_id' => $visitLog->id,
            'occurred_at' => now(),
            'metadata' => [
                'expected_checkout' => $visitLog->expected_checkout_at,
                'current_time' => now(),
            ],
        ]);
    }
}