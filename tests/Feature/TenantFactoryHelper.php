<?php
// Patch for RoleBasedSecurityTest: create a tenant with id=1 before running the test

namespace Tests\Feature;

use App\Models\Tenant;
use Illuminate\Support\Str;
use Tests\TestCase;

class TenantFactoryHelper
{
    public static function createTenantWithId1()
    {
        // Insert a tenant with id=1, a random uuid, and required fields directly into the DB
        $uuid = (string) Str::uuid();
        $now = now();
        \DB::table('tenants')->insert([
            'id' => 1,
            'uuid' => $uuid,
            'name' => 'Test Tenant',
            'domain' => 'tenant1.test',
            'data' => json_encode([
                'uuid' => $uuid,
                'name' => 'Test Tenant',
                'domain' => 'tenant1.test',
            ]),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
