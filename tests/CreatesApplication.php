<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $kernel = $app->make(Kernel::class);
        $kernel->bootstrap();

        // Run migrations and seed once when the application is created for tests.
        // Use testing env and force to avoid prompts in CI/local runs.
        $kernel->call('migrate:fresh', ['--env' => 'testing', '--force' => true]);
        $kernel->call('db:seed', ['--env' => 'testing', '--class' => \Database\Seeders\DatabaseSeeder::class]);

        return $app;
    }
}
