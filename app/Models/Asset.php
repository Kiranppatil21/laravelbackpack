<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Scopes\TenantScope;

class Asset extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;

    protected $fillable = [
        'tenant_uuid',
        'asset_name',
        'asset_code',
        'is_consumable',
        'stock_quantity',
        'unit',
        'min_stock_level',
        'max_stock_level',
        'reorder_level',
        'category',
        'description',
        'brand',
        'model',
        'serial_number',
        'barcode',
        'purchase_price',
        'purchase_date',
        'vendor_name',
        'vendor_contact',
        'current_value',
        'status',
        'condition',
        'location',
        'storage_location',
        'assigned_to_employee_id',
        'assigned_to_client_id',
        'assigned_date',
        'warranty_expiry',
        'next_maintenance_date',
        'maintenance_notes',
        'image_path',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'assigned_date' => 'date',
        'warranty_expiry' => 'date',
        'next_maintenance_date' => 'date',
        'purchase_price' => 'decimal:2',
        'current_value' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);
        
        static::creating(function ($asset) {
            if (empty($asset->tenant_uuid)) {
                if (function_exists('tenant') && tenant()) {
                    $asset->tenant_uuid = tenant()->id;
                } elseif (backpack_user() && backpack_user()->tenant_id) {
                    $asset->tenant_uuid = backpack_user()->tenant_id;
                } else {
                    $asset->tenant_uuid = 'default-uuid';
                }
            }
        });
    }

    public function assignedEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_to_employee_id');
    }

    public function assignedClient(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'assigned_to_client_id');
    }

    public static function getCategories()
    {
        return [
            'Electronics' => 'Electronics',
            'Furniture' => 'Furniture',
            'Vehicles' => 'Vehicles',
            'Equipment' => 'Equipment',
            'Tools' => 'Tools',
            'IT Hardware' => 'IT Hardware',
            'Office Supplies' => 'Office Supplies',
            'Security Equipment' => 'Security Equipment',
            'Communication Devices' => 'Communication Devices',
            'Other' => 'Other',
        ];
    }

    public static function getStatuses()
    {
        return [
            'available' => 'Available',
            'assigned' => 'Assigned',
            'maintenance' => 'Under Maintenance',
            'retired' => 'Retired',
            'lost' => 'Lost/Stolen',
        ];
    }

    public static function getConditions()
    {
        return [
            'excellent' => 'Excellent',
            'good' => 'Good',
            'fair' => 'Fair',
            'poor' => 'Poor',
        ];
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'available' => '<span class="badge badge-success">Available</span>',
            'assigned' => '<span class="badge badge-primary">Assigned</span>',
            'maintenance' => '<span class="badge badge-warning">Maintenance</span>',
            'retired' => '<span class="badge badge-secondary">Retired</span>',
            'lost' => '<span class="badge badge-danger">Lost/Stolen</span>',
        ];
        return $badges[$this->status] ?? $this->status;
    }
}
