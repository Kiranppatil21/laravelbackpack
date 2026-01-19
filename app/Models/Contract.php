<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    use \App\Models\Concerns\BelongsToTenant;
    use CrudTrait;

    protected $fillable = [
        'tenant_uuid',
        'contract_number',
        'client_id',
        'agency_id',
        'contract_type',
        'service_type',
        'start_date',
        'end_date',
        'duration_months',
        'number_of_guards',
        'shift_pattern',
        'monthly_contract_value',
        'per_guard_rate',
        'overtime_rate',
        'billing_cycle',
        'payment_terms_days',
        'status',
        'scope_of_work',
        'terms_and_conditions',
        'special_instructions',
        'contract_document',
        'signed_contract',
        'signed_date',
        'client_signatory',
        'agency_signatory',
        'auto_renewal',
        'renewal_notice_days',
        'renewal_reminder_sent',
        'renewed_from_contract_id',
        'cancellation_reason',
        'cancelled_date',
        'security_deposit',
        'deposit_refunded'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'signed_date' => 'date',
        'renewal_reminder_sent' => 'date',
        'cancelled_date' => 'date',
        'monthly_contract_value' => 'decimal:2',
        'per_guard_rate' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'auto_renewal' => 'boolean',
        'deposit_refunded' => 'boolean'
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function renewedFrom(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'renewed_from_contract_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('status', 'active')
            ->whereBetween('end_date', [now(), now()->addDays($days)]);
    }

    protected static function booted()
    {
        static::creating(function ($contract) {
            if (empty($contract->tenant_uuid)) {
                if (function_exists('tenant') && tenant()) {
                    $contract->tenant_uuid = tenant()->id;
                } elseif (backpack_user() && backpack_user()->tenant_id) {
                    $contract->tenant_uuid = backpack_user()->tenant_id;
                } else {
                    $contract->tenant_uuid = 'default-uuid';
                }
            }
        });
    }
}
