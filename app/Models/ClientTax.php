<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientTax extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'tax_status',
        'tax_type',
        'tax_percent',
        'tax_number',
    ];

    protected $casts = [
        'tax_percent' => 'decimal:2',
    ];

    /**
     * Get the client that owns the tax detail.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Scope a query to only include active tax records.
     */
    public function scopeActive($query)
    {
        return $query->where('tax_status', 'active');
    }

    /**
     * Scope a query to only include applicable tax records.
     */
    public function scopeApplicable($query)
    {
        return $query->where('tax_status', 'applicable');
    }

    /**
     * Get the tax types available in the system.
     */
    public static function getTaxTypes(): array
    {
        return [
            'GST' => 'Goods and Services Tax',
            'IGST' => 'Integrated GST',
            'CGST' => 'Central GST',
            'SGST' => 'State GST',
            'UTGST' => 'Union Territory GST',
            'SERVICE_TAX' => 'Service Tax',
            'VAT' => 'Value Added Tax',
            'CESS' => 'Cess',
        ];
    }

    /**
     * Get the tax status options.
     */
    public static function getStatusOptions(): array
    {
        return [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'applicable' => 'Applicable',
        ];
    }
}