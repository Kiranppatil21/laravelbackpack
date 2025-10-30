<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoUsersSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_users_are_seeded_and_can_login()
    {
        // seed only the demo users
        $this->seed(\Database\Seeders\DemoUsersSeeder::class);

        // pick one of the seeded roles and its expected email/password
        $email = 'super_admin@example.test';
        $password = 'password';

        // attempt API login
        $resp = $this->postJson('/api/login', [
            'email' => $email,
            'password' => $password,
        ]);

        $resp->assertStatus(200)->assertJsonStructure(['token', 'user']);

        // ensure returned user has the role (if spatie is available)
        if (class_exists('\Spatie\Permission\Models\Role')) {
            $token = $resp->json('token');
            $userResp = $this->withHeader('Authorization', 'Bearer '.$token)->getJson('/api/user');
            $userResp->assertStatus(200)->assertJsonStructure(['id', 'email', 'name', 'roles']);
            $this->assertEquals('Super Admin', $userResp->json('roles.0.name'));
        }
    }
}
