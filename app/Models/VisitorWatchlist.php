<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorWatchlist extends Model
{
    use HasFactory;

    protected $table = 'visitor_watchlist';

    protected $fillable = [
        'visitor_id', 'visitor_name', 'visitor_email', 'visitor_phone', 'visitor_id_value',
        'threat_level', 'reason', 'description', 'added_by', 'alert_on_entry', 'auto_deny',
        'expires_at', 'is_active'
    ];

    protected $casts = [
        'alert_on_entry' => 'boolean',
        'auto_deny' => 'boolean',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    // Relationships
    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function scopeHighThreat($query)
    {
        return $query->whereIn('threat_level', ['high', 'critical']);
    }

    public function scopeAutoDeny($query)
    {
        return $query->where('auto_deny', true);
    }

    // Helper methods
    public function isActive(): bool
    {
        return $this->is_active && 
               (is_null($this->expires_at) || $this->expires_at > now());
    }

    public function shouldAlertOnEntry(): bool
    {
        return $this->isActive() && $this->alert_on_entry;
    }

    public function shouldAutoDeny(): bool
    {
        return $this->isActive() && $this->auto_deny;
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    public function extend(\DateTime $newExpiresAt): void
    {
        $this->update(['expires_at' => $newExpiresAt]);
    }

    public static function checkVisitor(Visitor $visitor): ?self
    {
        return static::active()
            ->where(function ($query) use ($visitor) {
                $query->where('visitor_id', $visitor->id);
                
                if ($visitor->email) {
                    $query->orWhere('visitor_email', $visitor->email);
                }
                
                if ($visitor->phone) {
                    $query->orWhere('visitor_phone', $visitor->phone);
                }
                
                if ($visitor->id_value) {
                    $query->orWhere('visitor_id_value', $visitor->id_value);
                }
            })
            ->orderBy('threat_level', 'desc')
            ->first();
    }
}