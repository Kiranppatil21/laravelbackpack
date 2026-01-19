<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuPermission extends Model
{
    use \App\Models\Concerns\BelongsToTenant;
    use CrudTrait;

    protected $fillable = [
        'tenant_uuid',
        'menu_key',
        'menu_label',
        'menu_url',
        'parent_key',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
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

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            \Spatie\Permission\Models\Role::class,
            'role_menu_permissions',
            'menu_permission_id',
            'role_id'
        )->withPivot('can_access')->withTimestamps();
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuPermission::class, 'parent_key', 'menu_key')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function parent()
    {
        return $this->belongsTo(MenuPermission::class, 'parent_key', 'menu_key');
    }

    /**
     * Check if a role has access to this menu
     */
    public function canAccessByRole($roleId): bool
    {
        return $this->roles()
            ->where('role_id', $roleId)
            ->wherePivot('can_access', true)
            ->exists();
    }

    /**
     * Get all menu items accessible by a specific role
     */
    public static function getAccessibleMenusForRole($roleId)
    {
        return self::where('is_active', true)
            ->whereHas('roles', function ($query) use ($roleId) {
                $query->where('role_id', $roleId)
                      ->where('can_access', true);
            })
            ->whereNull('parent_key')
            ->with(['children' => function ($query) use ($roleId) {
                $query->whereHas('roles', function ($q) use ($roleId) {
                    $q->where('role_id', $roleId)
                      ->where('can_access', true);
                });
            }])
            ->orderBy('sort_order')
            ->get();
    }
}
