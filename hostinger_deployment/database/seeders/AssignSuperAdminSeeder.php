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
    // Prefer config() so env() isn't called outside config files (phpstan rule).
    // Tests or deploys can set these via config('super_admin.email') / config('super_admin.id') when needed.
    $email = config('super_admin.email');
    $id = config('super_admin.id');

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
