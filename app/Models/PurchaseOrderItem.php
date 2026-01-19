<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id', 'asset_id', 'item_name', 'description',
        'quantity', 'received_quantity', 'unit', 'unit_price',
        'tax_percentage', 'tax_amount', 'line_total'
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
