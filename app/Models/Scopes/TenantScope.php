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
     public function apply(Builder $builder, Model $model) {
        // If tenancy is initialized (per-tenant database), do not add a tenant_id WHERE
        if (function_exists('tenancy') && tenancy()->initialized()) {
            return;
        }

        if (auth()->check() && auth()->user()->tenant_id) {
            $builder->where('tenant_id', auth()->user()->tenant_id);
        }
    }
}
