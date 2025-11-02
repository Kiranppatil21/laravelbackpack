<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class TestRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure the 'Visitor' role exists for tests that expect it
    Role::firstOrCreate(['name' => 'Visitor']);

    // Also ensure any other minimal roles used by tests are present
    Role::firstOrCreate(['name' => 'Client']);
    Role::firstOrCreate(['name' => 'HR']);
    Role::firstOrCreate(['name' => 'Agency']);
    Role::firstOrCreate(['name' => 'Super Admin']);
    }
}
