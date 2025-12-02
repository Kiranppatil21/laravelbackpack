<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class VisitLog extends Model
{
    use HasFactory;

    protected $table = 'visit_logs';

    protected $fillable = [
        'visitor_id', 'host_id', 'check_in_at', 'check_out_at', 'source', 'notes', 'external_id',
        'visit_type', 'location', 'badge_number', 'escort_required', 'escorted_by',
        'security_items', 'nda_signed', 'nda_document_path',
        'expected_checkout_at', 'overstayed', 'checkout_reason',
        'entry_method', 'device_id', 'device_data',
        'entry_photo_path', 'exit_photo_path',
        'host_notified_at', 'security_alerted_at', 'alert_reasons',
        'visitor_rating', 'visitor_feedback', 'host_rating', 'host_feedback',
        'emergency_contact_notified', 'emergency_contact_notified_at', 'safety_incidents',
        'compliance_data', 'data_processed', 'data_retention_until'
    ];

    protected $casts = [
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        'expected_checkout_at' => 'datetime',
        'host_notified_at' => 'datetime',
        'security_alerted_at' => 'datetime',
        'emergency_contact_notified_at' => 'datetime',
        'data_retention_until' => 'datetime',
        'overstayed' => 'boolean',
        'nda_signed' => 'boolean',
        'emergency_contact_notified' => 'boolean',
        'data_processed' => 'boolean',
        'security_items' => 'array',
        'device_data' => 'array',
        'alert_reasons' => 'array',
        'compliance_data' => 'array',
    ];

    protected $dates = [
        'check_in_at', 'check_out_at', 'expected_checkout_at',
        'host_notified_at', 'security_alerted_at', 'emergency_contact_notified_at',
        'data_retention_until'
    ];

    // Relationships
    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function escortedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'escorted_by');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(VisitorDevice::class, 'device_id', 'device_id');
    }

    public function invitation(): HasOne
    {
        return $this->hasOne(VisitorInvitation::class);
    }

    public function securityAlerts(): HasMany
    {
        return $this->hasMany(SecurityAlert::class);
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(VisitorFeedback::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereNull('check_out_at');
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('check_out_at');
    }

    public function scopeOverstayed($query)
    {
        return $query->where('overstayed', true)
            ->orWhere(function ($q) {
                $q->whereNotNull('expected_checkout_at')
                  ->where('expected_checkout_at', '<', now())
                  ->whereNull('check_out_at');
            });
    }

    public function scopeToday($query)
    {
        return $query->whereDate('check_in_at', today());
    }

    public function scopeByEntryMethod($query, string $method)
    {
        return $query->where('entry_method', $method);
    }

    public function scopeRequiresEscort($query)
    {
        return $query->whereNotNull('escort_required');
    }

    // Helper methods
    public function isActive(): bool
    {
        return is_null($this->check_out_at);
    }

    public function isCompleted(): bool
    {
        return !is_null($this->check_out_at);
    }

    public function getDuration(): ?int
    {
        if ($this->isActive()) {
            return $this->check_in_at->diffInMinutes(now());
        }
        
        return $this->check_in_at?->diffInMinutes($this->check_out_at);
    }

    public function getDurationHuman(): string
    {
        $minutes = $this->getDuration();
        if (!$minutes) return 'N/A';

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($hours > 0) {
            return "{$hours}h {$remainingMinutes}m";
        }

        return "{$remainingMinutes}m";
    }

    public function checkForOverstay(): void
    {
        if ($this->isActive() && 
            $this->expected_checkout_at && 
            $this->expected_checkout_at < now() && 
            !$this->overstayed) {
            
            $this->update(['overstayed' => true]);
            
            // Create security alert
            SecurityAlert::createOverstayAlert($this);
        }
    }

    public function checkout(string $reason = null, User $checkoutBy = null): void
    {
        $this->update([
            'check_out_at' => now(),
            'checkout_reason' => $reason,
        ]);

        // Mark invitation as used if applicable
        if ($this->invitation) {
            $this->invitation->markAsUsed($this);
        }
    }

    public function extendVisit(\DateTime $newExpectedCheckout): void
    {
        $this->update([
            'expected_checkout_at' => $newExpectedCheckout,
            'overstayed' => false, // Reset overstay status
        ]);
    }

    public function addSecurityItem(string $item): void
    {
        $items = $this->security_items ?? [];
        $items[] = $item;
        $this->update(['security_items' => array_unique($items)]);
    }

    public function removeSecurityItem(string $item): void
    {
        $items = $this->security_items ?? [];
        $items = array_filter($items, fn($i) => $i !== $item);
        $this->update(['security_items' => array_values($items)]);
    }

    public function notifyHost(): void
    {
        if ($this->host && is_null($this->host_notified_at)) {
            // Send notification to host
            $this->update(['host_notified_at' => now()]);
            
            // You could trigger a notification here
            // $this->host->notify(new VisitorCheckedIn($this->visitor, $this));
        }
    }

    public function alertSecurity(array $reasons = []): void
    {
        $this->update([
            'security_alerted_at' => now(),
            'alert_reasons' => $reasons,
        ]);
    }

    public function markDataProcessed(): void
    {
        $this->update([
            'data_processed' => true,
            'data_retention_until' => now()->addYears(2), // Adjust based on compliance requirements
        ]);
    }

    public function getEntryMethodBadge(): string
    {
        return match ($this->entry_method) {
            'qr_code' => '📱 QR Code',
            'rfid' => '💳 RFID',
            'biometric' => '👆 Biometric',
            'manual' => '✍️ Manual',
            default => '❓ Unknown'
        };
    }

    public function getStatusBadge(): string
    {
        if ($this->isCompleted()) {
            return '✅ Completed';
        }
        
        if ($this->overstayed || ($this->expected_checkout_at && $this->expected_checkout_at < now())) {
            return '⚠️ Overstayed';
        }
        
        return '🟢 Active';
    }
}
