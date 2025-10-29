<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Domain as StanclDomain;

/**
 * App Domain model that keeps compatibility with the existing central
 * `domains.tenant_id` integer FK (points to tenants.id). Stancl's
 * default Domain model expects tenant_id to reference the tenant primary
 * key (which we've mapped to `uuid`), so we override the relationship
 * to resolve tenants by the integer `id` column for now.
 */
class Domain extends StanclDomain
{
    /**
     * Resolve the tenant relation using the integer `id` column on tenants
     * to remain compatible with the existing domains.tenant_id FK.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }
}
