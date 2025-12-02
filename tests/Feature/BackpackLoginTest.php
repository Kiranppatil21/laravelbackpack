<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class BackpackLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_log_in_to_backpack(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $user = User::factory()->create([
            'email' => 'super_admin@example.test',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole('Super Admin');

        $response = $this->post(route('backpack.auth.login'), [
            'email' => 'super_admin@example.test',
            'password' => 'password',
        ]);

        $response->assertRedirect(backpack_url('dashboard'));
        $this->assertAuthenticatedAs($user, 'backpack');
    }
}
