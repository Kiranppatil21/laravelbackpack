<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'manage users',
            'create listings',
            'edit listings',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Create roles
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $agencyOwner = Role::firstOrCreate(['name' => 'Agency Owner']);

        // Assign permissions to roles
        $agencyOwner->givePermissionTo('create listings');

        // Super Admin gets all permissions
        $superAdmin->givePermissionTo(Permission::all());
    }
}
