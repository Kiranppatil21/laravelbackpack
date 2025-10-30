<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Client;

class EmployeeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_user_can_create_employee()
    {
        Storage::fake('local');

        $tenantId = \Illuminate\Support\Facades\DB::table('tenants')->insertGetId([
            'name' => 'Test Tenant',
            'domain' => 'test.example',
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $client = Client::factory()->create(['tenant_id' => $tenantId]);
    $user = User::factory()->create();
    $user->tenant_id = $tenantId;

        $response = $this->actingAs($user)->postJson('/api/employees', [
            'first_name' => 'Test',
            'last_name' => 'Employee',
            'client_id' => $client->id,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('employees', ['first_name' => 'Test', 'client_id' => $client->id]);
    }

    public function test_user_without_tenant_cannot_create_employee()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/employees', [
            'first_name' => 'Test',
            'last_name' => 'Employee',
        ])->assertStatus(403);
    }

    public function test_cannot_assign_employee_to_foreign_client()
    {
        $tenantAId = \Illuminate\Support\Facades\DB::table('tenants')->insertGetId([
            'name' => 'A',
            'domain' => 'a.example',
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tenantBId = \Illuminate\Support\Facades\DB::table('tenants')->insertGetId([
            'name' => 'B',
            'domain' => 'b.example',
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $clientB = Client::factory()->create(['tenant_id' => $tenantBId]);

    $user = User::factory()->create();
    $user->tenant_id = $tenantAId;

        $response = $this->actingAs($user)->postJson('/api/employees', [
            'first_name' => 'Cross',
            'last_name' => 'Tenant',
            'client_id' => $clientB->id,
        ]);

        $response->assertStatus(422);
    }
}
