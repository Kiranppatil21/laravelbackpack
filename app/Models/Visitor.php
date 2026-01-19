<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Visitor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'company', 'id_type', 'id_value', 'host_id', 'source',
        'photo_path', 'qr_code', 'status', 'notes',
        'id_verified', 'id_verified_at', 'verified_by',
        'emergency_contact_name', 'emergency_contact_phone', 'address',
        'purpose', 'pre_approved', 'approved_by', 'approved_at',
        'background_check_required', 'background_check_status', 'background_check_date',
        'on_watchlist', 'watchlist_reason',
        'temperature', 'health_screening_passed', 'health_questions',
        'metadata'
    ];

    protected $casts = [
        'id_verified' => 'boolean',
        'id_verified_at' => 'datetime',
        'approved_at' => 'datetime',
        'background_check_date' => 'datetime',
        'pre_approved' => 'boolean',
        'background_check_required' => 'boolean',
        'on_watchlist' => 'boolean',
        'health_screening_passed' => 'boolean',
        'health_questions' => 'array',
        'metadata' => 'array',
        'temperature' => 'decimal:2',
    ];

    protected $dates = [
        'id_verified_at',
        'approved_at',
        'background_check_date',
    ];

    // Automatically generate QR code when creating visitor
    protected static function booted(): void
    {
        static::creating(function (Visitor $visitor) {
            if (empty($visitor->qr_code)) {
                $visitor->qr_code = 'VIS-' . strtoupper(Str::random(10));
            }
        });
    }

    // Relationships
    public function visitLogs(): HasMany
    {
        return $this->hasMany(VisitLog::class);
    }

    public function currentVisit(): HasOne
    {
        return $this->hasOne(VisitLog::class)->whereNull('check_out_at')->latest('check_in_at');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function watchlistEntries(): HasMany
    {
        return $this->hasMany(VisitorWatchlist::class);
    }

    public function activeWatchlistEntry(): HasOne
    {
        return $this->hasOne(VisitorWatchlist::class)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(VisitorInvitation::class, 'visitor_email', 'email');
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(VisitorFeedback::class);
    }

    public function securityAlerts(): HasMany
    {
        return $this->hasMany(SecurityAlert::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeBlocked($query)
    {
        return $query->where('status', 'blocked');
    }

    public function scopeOnWatchlist($query)
    {
        return $query->where('on_watchlist', true);
    }

    public function scopePreApproved($query)
    {
        return $query->where('pre_approved', true);
    }

    public function scopeIdVerified($query)
    {
        return $query->where('id_verified', true);
    }

    // Helper methods
    public function isCurrentlyVisiting(): bool
    {
        return $this->currentVisit()->exists();
    }

    public function isOnWatchlist(): bool
    {
        return $this->activeWatchlistEntry()->exists();
    }

    public function isPreApproved(): bool
    {
        return $this->pre_approved && $this->status === 'active';
    }

    public function requiresBackgroundCheck(): bool
    {
        return $this->background_check_required && 
               $this->background_check_status !== 'passed';
    }

    public function passedHealthScreening(): bool
    {
        return $this->health_screening_passed && 
               ($this->temperature === null || $this->temperature < 37.5);
    }

    public function canCheckIn(): bool
    {
        return $this->status === 'active' &&
               !$this->isOnWatchlist() &&
               $this->passedHealthScreening() &&
               !$this->requiresBackgroundCheck();
    }

    public function getFullNameAttribute(): string
    {
        return $this->name . ($this->company ? " ({$this->company})" : '');
    }

    public function getContactInfoAttribute(): string
    {
        $info = [];
        if ($this->email) $info[] = $this->email;
        if ($this->phone) $info[] = $this->phone;
        return implode(' | ', $info);
    }

    public function generateQrCode(): string
    {
        if (empty($this->qr_code)) {
            $this->qr_code = 'VIS-' . strtoupper(Str::random(10));
            $this->save();
        }
        return $this->qr_code;
    }

    public function markAsVerified(User $verifier): void
    {
        $this->update([
            'id_verified' => true,
            'id_verified_at' => now(),
            'verified_by' => $verifier->id,
        ]);
    }

    public function approve(User $approver): void
    {
        $this->update([
            'pre_approved' => true,
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'status' => 'active',
        ]);
    }

    public function addToWatchlist(string $reason, string $threatLevel = 'medium', ?User $addedBy = null): void
    {
        $this->update([
            'on_watchlist' => true,
            'watchlist_reason' => $reason,
        ]);

        if (class_exists(VisitorWatchlist::class)) {
            VisitorWatchlist::create([
                'visitor_id' => $this->id,
                'visitor_name' => $this->name,
                'visitor_email' => $this->email,
                'visitor_phone' => $this->phone,
                'visitor_id_value' => $this->id_value,
                'threat_level' => $threatLevel,
                'reason' => 'other',
                'description' => $reason,
                'added_by' => $addedBy?->id,
            ]);
        }
    }
}
