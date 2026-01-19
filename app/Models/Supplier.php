<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;

class Supplier extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;

    protected $fillable = [
        'tenant_uuid', 'supplier_code', 'company_name', 'contact_person',
        'email', 'phone', 'alternate_phone', 'address', 'city', 'state',
        'pincode', 'gstin', 'pan_number', 'category', 'payment_terms',
        'credit_limit', 'outstanding_amount', 'status', 'notes'
    ];

    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);
        static::creating(function ($supplier) {
            if (empty($supplier->tenant_uuid)) {
                $supplier->tenant_uuid = tenant() ? tenant()->id : 'default-uuid';
            }
        });
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
