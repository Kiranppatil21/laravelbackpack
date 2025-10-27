<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_valid_credentials_returns_token()
    {
        $password = 'password';
        $user = User::factory()->create([ 'password' => $password ]);

        $resp = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $resp->assertStatus(200)->assertJsonStructure(['token', 'user']);
    }

    public function test_protected_dashboard_requires_token()
    {
        $resp = $this->getJson('/api/dashboard');
        $resp->assertStatus(401);
    }

    public function test_access_dashboard_with_token_and_role()
    {
        $password = 'password';
        $user = User::factory()->create([ 'password' => $password ]);
        // create role if spatie exists
        if (class_exists('\Spatie\Permission\Models\Role')) {
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Visitor']);
            $user->assignRole('Visitor');
        }

        $token = $user->createToken('test')->plainTextToken;

        $resp = $this->withHeader('Authorization', 'Bearer '.$token)->getJson('/api/dashboard');
        $resp->assertStatus(200)->assertJsonStructure(['message', 'roles']);
    }
}
