<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_seeder_creates_expected_roles()
    {
        // Run the RoleSeeder
        Artisan::call('db:seed', ['--class' => 'RoleSeeder']);

        $expected = [
            'Super Admin',
            'Agency Owner',
            'HR',
            'Client',
            'Guard/Employee',
            'Visitor',
            'Police',
        ];

        foreach ($expected as $roleName) {
            $this->assertTrue(Role::where('name', $roleName)->exists(), "Role {$roleName} was not seeded");
        }
    }
}
