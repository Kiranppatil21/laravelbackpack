<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Seed core app roles & permissions used by tests and app logic
        if (class_exists(\Database\Seeders\RoleSeeder::class)) {
            $this->call(\Database\Seeders\RoleSeeder::class);
        }

        // Run demo data by default for local/dev environments
        if (class_exists(\Database\Seeders\DemoSeeder::class) || class_exists(\Database\Seeders\DemoUsersSeeder::class)) {
            $this->call(\Database\Seeders\DemoSeeder::class);
        }

        $this->call([
            \Database\Seeders\DemoUsersSeeder::class,
        ]);
    }
}
