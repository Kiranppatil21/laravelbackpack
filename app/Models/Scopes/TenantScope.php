<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model)
    {
        // If tenancy is initialized (per-tenant database), do not add a tenant_id WHERE
        if (function_exists('tenancy') && tenancy()->initialized) {
            return;
        }

        // Check both web and backpack auth guards for tenant isolation
        $user = null;
        if (auth()->check()) {
            $user = auth()->user();
        } elseif (function_exists('backpack_auth') && backpack_auth()->check()) {
            $user = backpack_auth()->user();
        }

        if ($user && $user->tenant_id) {
            $builder->where('tenant_id', $user->tenant_id);
        }
    }
}
