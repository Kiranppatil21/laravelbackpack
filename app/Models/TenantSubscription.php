<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantSubscription extends Model
{
    protected $table = 'tenant_subscriptions';

    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'stripe_customer_id',
        'price_id',
        'status',
        'raw',
    ];

    protected $casts = [
        'raw' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(\Stancl\Tenancy\Database\Models\Tenant::class, 'tenant_id');
    }
}
