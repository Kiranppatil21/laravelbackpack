<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as StanclTenant;

/**
 * App Tenant model that uses `uuid` as the tenant identifier.
 * This keeps the integer `id` column for compatibility while enabling
 * stancl/tenancy to use `uuid` as the tenant key for new tenants.
 */
class Tenant extends StanclTenant
{
    /**
     * Use the `uuid` column as the tenant primary key for stancl usage.
     * We keep the integer `id` column intact in the DB to avoid breaking FKs.
     */
    protected $primaryKey = 'uuid';

    /**
     * Keys are non-incrementing strings (UUIDs).
     */
    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * Tell the tenant model which attribute is the tenant key name.
     */
    public function getTenantKeyName(): string
    {
        return $this->getKeyName(); // will return 'uuid'
    }
}

