<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleVisibilityTest extends TestCase
{
    public function test_roles_are_seeded_and_visible()
    {
        $roles = Role::all()->pluck('name')->toArray();
        echo "Seeded roles: ".implode(', ', $roles)."\n";
        $this->assertContains('Super Admin', $roles);
        $this->assertContains('Agency Owner', $roles);
    }
}
