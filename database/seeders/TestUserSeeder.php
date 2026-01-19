<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test user for frontend testing
        User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Test Admin',
                'email' => 'admin@test.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'visitor@test.com'],
            [
                'name' => 'Test Visitor',
                'email' => 'visitor@test.com', 
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Test users created:');
        $this->command->info('- admin@test.com / password');
        $this->command->info('- visitor@test.com / password');
    }
}
