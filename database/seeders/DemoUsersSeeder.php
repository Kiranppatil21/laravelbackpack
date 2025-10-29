<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Super Admin',
            'Agency Owner',
            'HR',
            'Client',
            'Guard/Employee',
            'Visitor',
            'Police',
        ];

        foreach ($roles as $role) {
            $email = strtolower(str_replace(' ', '_', $role)).'@example.test';
            $user = User::firstOrCreate(
                ['email' => $email],
                ['name' => $role.' Demo', 'password' => Hash::make('password')]
            );
            $r = Role::firstOrCreate(['name' => $role]);
            if (! $user->hasRole($role)) {
                $user->assignRole($role);
            }
        }
    }
}
