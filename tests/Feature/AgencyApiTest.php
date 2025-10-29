<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AgencyApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_user_can_create_agency()
    {
        // Insert a central tenant row and assign to user
        $tenantId = DB::table('tenants')->insertGetId([
            'name' => 'Test Tenant',
            'domain' => 't.test',
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

    $user = User::factory()->create();
    // attach tenant_id in-memory for authorization checks (not persisted on users table)
    $user->tenant_id = $tenantId;
    $this->actingAs($user);

        $resp = $this->postJson('/api/agencies', ['name' => 'New Agency']);

        $resp->assertStatus(201);

        $this->assertDatabaseHas('agencies', ['name' => 'New Agency', 'tenant_id' => $tenantId]);
    }

    public function test_user_without_tenant_cannot_create_agency()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $resp = $this->postJson('/api/agencies', ['name' => 'Blocked Agency']);

        $resp->assertStatus(403);

        $this->assertDatabaseMissing('agencies', ['name' => 'Blocked Agency']);
    }
}
