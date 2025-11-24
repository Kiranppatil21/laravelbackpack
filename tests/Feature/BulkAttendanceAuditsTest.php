<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Client;
use App\Models\EmployeeAttendanceMaster;

class BulkAttendanceAuditsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Run central migrations (handled by RefreshDatabase)
    }

    protected function initializeTenant(): Tenant
    {
        $tenant = Tenant::create([
            'uuid' => Str::uuid()->toString(),
            'id' => null, // integer id auto-increment if present
            'data' => []
        ]);

        tenancy()->initialize($tenant);
        // Run tenant migrations into tenant DB connection
        Artisan::call('migrate', [
            '--path' => database_path('migrations/tenant'),
            '--realpath' => true,
        ]);

        return $tenant;
    }

    protected function createAdminUser(): User
    {
        $role = Role::firstOrCreate(['name' => 'Super Admin']);
        $user = User::first();
        if (! $user) {
            $user = User::create([
                'name' => 'Test Admin',
                'email' => 'admin@example.com',
                'password' => bcrypt('secret'),
            ]);
        }
        if (! $user->hasRole('Super Admin')) {
            $user->assignRole('Super Admin');
        }
        return $user;
    }

    public function test_audit_endpoint_returns_expected_actions(): void
    {
        $tenant = $this->initializeTenant();
        $user = $this->createAdminUser();
        $this->actingAs($user); // Backpack admin guard defaults to web

        // Create site/client
        $client = Client::create(['name' => 'Audit Site', 'email' => 'site@example.com']);

        // Create master record (draft)
        $month = now()->format('Y-m');
        $master = EmployeeAttendanceMaster::create([
            'tenant_id' => 1,
            'site_id' => $client->id,
            'month' => $month,
            'user_type' => 'Security Guard',
            'created_by' => $user->id,
            'status' => 'draft',
        ]);

        // Transition: submit
        $this->postJson(route('admin.bulk-attendance.submit', $master->id))
            ->assertStatus(200)
            ->assertJson(['success' => true]);
        $master->refresh();
        $this->assertEquals('submitted', $master->status);

        // Transition: approve
        $this->postJson(route('admin.bulk-attendance.approve', $master->id))
            ->assertStatus(200)
            ->assertJson(['success' => true]);
        $master->refresh();
        $this->assertEquals('approved', $master->status);

        // Transition: lock
        $this->postJson(route('admin.bulk-attendance.lock', $master->id))
            ->assertStatus(200)
            ->assertJson(['success' => true]);
        $master->refresh();
        $this->assertEquals('locked', $master->status);

        // Fetch audits
        $resp = $this->getJson(route('admin.bulk-attendance.audits', $master->id))
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $payload = $resp->json();
        $this->assertArrayHasKey('audits', $payload);
        $actions = collect($payload['audits'])->pluck('action')->toArray();

        // Expect at least submit, approve, lock (create may or may not be logged depending on prior save logic)
        $this->assertTrue(in_array('submit', $actions), 'Submit action missing');
        $this->assertTrue(in_array('approve', $actions), 'Approve action missing');
        $this->assertTrue(in_array('lock', $actions), 'Lock action missing');
    }
}
