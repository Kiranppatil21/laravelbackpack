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
}
