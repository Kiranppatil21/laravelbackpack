<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class AdminCrudTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // Ensure Backpack uses the web guard in tests
        config(['backpack.base.guard' => 'web']);

        // create Super Admin role
        Role::create(['name' => 'Super Admin']);
    }

    public function test_admin_agency_index_shows_seeded_agency()
    {
        // create user and assign role
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        // create an agency via factory
        $agency = Agency::factory()->create(['name' => 'Acme Testing Agency']);

        $response = $this->actingAs($user)->get('/admin/agency');
        $response->assertStatus(200);

        // Backpack list loads rows via AJAX (POST to agency/search). Disable CSRF for this test POST.
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $ajax = $this->actingAs($user)->post('/admin/agency/search', []);
        $ajax->assertStatus(200);
        $ajax->assertSee('Acme Testing Agency');
    }
}
