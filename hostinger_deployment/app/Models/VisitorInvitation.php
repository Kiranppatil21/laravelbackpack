<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class VisitorInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'invitation_code', 'visitor_name', 'visitor_email', 'visitor_phone', 'visitor_company',
        'host_id', 'invited_by', 'purpose', 'valid_from', 'valid_until', 'status',
        'access_areas', 'special_instructions', 'escort_required', 'required_documents',
        'used_at', 'visit_log_id'
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'used_at' => 'datetime',
        'access_areas' => 'array',
        'required_documents' => 'array',
        'escort_required' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (VisitorInvitation $invitation) {
            if (empty($invitation->invitation_code)) {
                $invitation->invitation_code = 'INV-' . strtoupper(Str::random(8));
            }
        });
    }

    // Relationships
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function visitLog(): BelongsTo
    {
        return $this->belongsTo(VisitLog::class);
    }

    // Scopes
    public function scopeValid($query)
    {
        return $query->where('status', 'pending')
            ->where('valid_from', '<=', now())
            ->where('valid_until', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('valid_until', '<', now())
            ->where('status', '!=', 'used');
    }

    // Helper methods
    public function isValid(): bool
    {
        return $this->status === 'pending' &&
               $this->valid_from <= now() &&
               $this->valid_until >= now();
    }

    public function markAsUsed(VisitLog $visitLog): void
    {
        $this->update([
            'status' => 'used',
            'used_at' => now(),
            'visit_log_id' => $visitLog->id,
        ]);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    public function extend(\DateTime $newValidUntil): void
    {
        $this->update(['valid_until' => $newValidUntil]);
    }
}