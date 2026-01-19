<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleMenuPermission extends Model
{
    use \App\Models\Concerns\BelongsToTenant;

    protected $fillable = [
        'tenant_uuid',
        'role_id',
        'menu_permission_id',
        'can_access',
    ];

    protected $casts = [
        'can_access' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->tenant_uuid)) {
                if (function_exists('tenant') && tenant()) {
                    $model->tenant_uuid = tenant()->id;
                } elseif (backpack_user() && backpack_user()->tenant_id) {
                    $model->tenant_uuid = backpack_user()->tenant_id;
                } else {
                    $model->tenant_uuid = 'default-uuid';
                }
            }
        });
    }

    public function role()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class);
    }

    public function menuPermission()
    {
        return $this->belongsTo(MenuPermission::class);
    }
}
