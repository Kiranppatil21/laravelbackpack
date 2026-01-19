<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;

class PurchaseOrder extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;

    protected $fillable = [
        'tenant_uuid', 'po_number', 'supplier_id', 'order_date', 'expected_delivery_date',
        'actual_delivery_date', 'status', 'subtotal', 'tax_amount', 'discount_amount',
        'shipping_cost', 'total_amount', 'payment_status', 'paid_amount',
        'shipping_address', 'terms_conditions', 'notes', 'created_by', 'approved_by', 'approved_at'
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'approved_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);
        static::creating(function ($po) {
            if (empty($po->tenant_uuid)) {
                $po->tenant_uuid = tenant() ? tenant()->id : 'default-uuid';
            }
        });
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}
