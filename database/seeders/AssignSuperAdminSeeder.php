<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AssignSuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = env('SUPER_ADMIN_EMAIL');
        $id = env('SUPER_ADMIN_ID');

        $user = null;

        if ($email) {
            $user = \App\Models\User::where('email', $email)->first();
        }

        if (! $user && $id) {
            $user = \App\Models\User::find($id);
        }

        if (! $user) {
            $user = \App\Models\User::find(1);
        }

        if (! $user) {
            $this->command->info('No user found to assign Super Admin role.');
            return;
        }

        $roleName = 'Super Admin';

        // Ensure role exists
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => $roleName]);

        $user->assignRole($roleName);

        $this->command->info("Assigned role '{$roleName}' to user: {$user->id} ({$user->email})");
    }
}
