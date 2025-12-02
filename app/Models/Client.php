<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property string|null $tenant_uuid
 */
class Client extends Authenticatable
{
    use \App\Models\Concerns\BelongsToTenant;
    use CrudTrait;
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'tenant_id',
        'tenant_uuid',
        'agency_id',
        // New fields for comprehensive client management
        'company_id',
        'serial_no',
        'name_of_client',
        'to_title',
        'site_name',
        'address',
        'dob',
        'date_of_anniversary',
        'contact_no_1',
        'contact_no_2',
        'site_supervisor_contact',
        'site_admin_contact',
        'site_manager_contact',
        'gst_no',
        'tds_percentage',
        'pan_no',
        'primary_email_1',
        'primary_email_2',
        'additional_charges',
        'additional_charges_comment',
        // Financial / billing fields for security-guard business
        'billing_rate',
        'salary_cost',
        'esi_rate',
        'pf_rate',
        'licensing_cost',
        'administrative_overhead',
        'password',
        'status',
        'sms_reports',
        'sms_attendance',
        'sms_bill',
        'sms_bill_reminder',
        'sms_payment_receipt',
        'email_reports',
        'email_attendance',
        'email_bill',
        'email_bill_reminder',
        'email_payment_receipt',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'dob' => 'date',
        'date_of_anniversary' => 'date',
        'tds_percentage' => 'decimal:2',
        'additional_charges' => 'decimal:2',
        'billing_rate' => 'decimal:2',
        'salary_cost' => 'decimal:2',
        'esi_rate' => 'decimal:2',
        'pf_rate' => 'decimal:2',
        'licensing_cost' => 'decimal:2',
        'administrative_overhead' => 'decimal:2',
        // 'gross_margin' is computed dynamically; do not cast/store as static source of truth
        'sms_reports' => 'boolean',
        'sms_attendance' => 'boolean',
        'sms_bill' => 'boolean',
        'sms_bill_reminder' => 'boolean',
        'sms_payment_receipt' => 'boolean',
        'email_reports' => 'boolean',
        'email_attendance' => 'boolean',
        'email_bill' => 'boolean',
        'email_bill_reminder' => 'boolean',
        'email_payment_receipt' => 'boolean',
        'password' => 'hashed',
    ];

    /**
     * Always append computed attributes to model arrays.
     * @var array
     */
    protected $appends = [
        'gross_margin',
    ];

    public function agency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }

    public function employees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Employee::class, 'client_id');
    }

    /**
     * Get the company that owns the client.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the contacts for the client.
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(ClientContact::class);
    }

    /**
     * Get the tax details for the client.
     */
    public function taxes(): HasMany
    {
        return $this->hasMany(ClientTax::class);
    }

    /**
     * Get the next serial number for the client.
     */
    public static function getNextSerialNumber(): int
    {
        return static::max('serial_no') + 1;
    }

    /**
     * Scope a query to only include active clients.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get the username for authentication (using email).
     */
    public function getAuthIdentifierName()
    {
        return 'primary_email_1';
    }

    /**
     * Get the full display name.
     */
    public function getFullNameAttribute(): string
    {
        return ($this->to_title ? $this->to_title . ' ' : '') . ($this->name_of_client ?: $this->name);
    }

    /**
     * Boot method to auto-assign serial numbers.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($client) {
            if (!$client->serial_no) {
                $client->serial_no = static::getNextSerialNumber();
            }
        });
    }

    /**
     * Get tax types for dropdown
     */
    public static function getTaxTypes()
    {
        return [
            'gst' => 'GST (Goods & Services Tax)',
            'vat' => 'VAT (Value Added Tax)',
            'tds' => 'TDS (Tax Deducted at Source)',
            'service_tax' => 'Service Tax',
            'cess' => 'Cess',
            'income_tax' => 'Income Tax',
            'property_tax' => 'Property Tax',
            'other' => 'Other Tax'
        ];
    }

    /**
     * Get tax statuses for dropdown
     */
    public static function getTaxStatuses()
    {
        return [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'pending' => 'Pending',
            'expired' => 'Expired'
        ];
    }

    /**
     * Compute gross margin for a client based on billing and cost components.
     * Formula (monetary): billing_rate - (salary_cost + licensing_cost + administrative_overhead + esi_amount + pf_amount)
     * ESI/PF are applied as percentages against `salary_cost` when present.
     *
     * @return float
     */
    public function getGrossMarginAttribute(): float
    {
        $billing = $this->billing_rate !== null ? (float) $this->billing_rate : 0.0;
        $salary = $this->salary_cost !== null ? (float) $this->salary_cost : 0.0;
        $licensing = $this->licensing_cost !== null ? (float) $this->licensing_cost : 0.0;
        $overhead = $this->administrative_overhead !== null ? (float) $this->administrative_overhead : 0.0;

        $esiAmount = 0.0;
        if ($this->esi_rate !== null) {
            $esiAmount = $salary * ((float) $this->esi_rate / 100.0);
        }

        $pfAmount = 0.0;
        if ($this->pf_rate !== null) {
            $pfAmount = $salary * ((float) $this->pf_rate / 100.0);
        }

        $totalCosts = $salary + $licensing + $overhead + $esiAmount + $pfAmount;

        return round($billing - $totalCosts, 2);
    }
}
