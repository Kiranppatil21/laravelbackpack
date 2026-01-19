<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;

class InventoryTransaction extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;

    protected $fillable = [
        'tenant_uuid', 'transaction_type', 'reference_type', 'reference_id',
        'asset_id', 'quantity', 'unit', 'unit_cost', 'total_cost',
        'from_location', 'to_location', 'issued_to_employee_id',
        'issued_to_client_id', 'transaction_date', 'remarks', 'created_by'
    ];

    protected $casts = [
        'transaction_date' => 'date',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);
        static::creating(function ($transaction) {
            if (empty($transaction->tenant_uuid)) {
                $transaction->tenant_uuid = tenant() ? tenant()->id : 'default-uuid';
            }
        });
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function issuedToEmployee()
    {
        return $this->belongsTo(Employee::class, 'issued_to_employee_id');
    }

    public function issuedToClient()
    {
        return $this->belongsTo(Client::class, 'issued_to_client_id');
    }
}
