<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stancl\Tenancy\Database\Models\Tenant;
use Tests\TestCase;

class AdminActivateTenantTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_activate_tenant()
    {
        // create a user and mark as admin via CheckIfAdmin (default in this repo always allows)
        $admin = User::factory()->create();

        // create a tenant record (inactive) using DB so we get integer id matching migrations
        $tenantId = \Illuminate\Support\Facades\DB::table('tenants')->insertGetId([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Acme Corp',
            'domain' => 'acme.example.test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tenant = Tenant::find($tenantId);

        // Call the controller action directly via the container to avoid any route binding / middleware interference in tests
        $this->actingAs($admin);
        $controller = app()->make(\App\Http\Controllers\Admin\TenantCrudController::class);
        app()->call([$controller, 'activate'], ['tenant' => $tenant]);

        // Refreshing the Stancl Tenant model can be inconsistent with direct DB updates
        // in some test environments, so assert against the DB directly.
        $this->assertDatabaseHas('tenants', [
            'id' => $tenantId,
            'active' => 1,
        ]);
        $this->assertDatabaseMissing('tenants', [
            'id' => $tenantId,
            'activated_at' => null,
        ]);
    }
}
