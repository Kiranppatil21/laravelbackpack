<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PermissionsSmokeTest extends TestCase
{
    /**
     * Basic smoke test that runs migrations and seeds the RoleSeeder using the testing DB.
     * This runs in the PHPUnit environment (should use in-memory sqlite if configured in phpunit.xml).
     *
     * @return void
     */
    public function test_migrate_and_seed_roles()
    {
        // Run only the Spatie permissions migration file via Artisan --path to avoid unrelated migrations
        $path = 'database/migrations/2025_10_21_045714_create_permission_tables.php';
        $this->assertFileExists(base_path($path), 'Permissions migration file not found: ' . base_path($path));

        $migrate = Artisan::call('migrate', ['--path' => $path, '--force' => true]);
        $this->assertEquals(0, $migrate, 'Permissions migration did not complete successfully');

        // Seed roles
        $seed = Artisan::call('db:seed', ['--class' => 'RoleSeeder']);

        $this->assertEquals(0, $seed, 'RoleSeeder did not complete successfully');
    }
}
