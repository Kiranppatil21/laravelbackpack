<?php
/**
 * Database Migration Script for Hostinger
 * Upload this to your Hostinger public_html folder
 * Visit: https://yourdomain.com/run_migrations.php
 * DELETE THIS FILE after use!
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
set_time_limit(300);

echo "<!DOCTYPE html><html><head><title>Run Migrations</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#1e1e1e;color:#00ff00;}</style></head><body>";
echo "<h1>🗄️ Database Migration</h1>";

try {
    require __DIR__.'/vendor/autoload.php';
    $app = require_once __DIR__.'/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    
    echo "<p>Running migrations...</p>";
    $status = $kernel->call('migrate', ['--force' => true]);
    echo "<p style='color:" . ($status === 0 ? 'lime' : 'red') . "'>Migrations: " . ($status === 0 ? 'SUCCESS ✓' : 'FAILED ✗') . "</p>";
    
    echo "<p>Running seeders...</p>";
    $status = $kernel->call('db:seed', ['--force' => true, '--class' => 'RoleSeeder']);
    echo "<p style='color:" . ($status === 0 ? 'lime' : 'red') . "'>Role Seeder: " . ($status === 0 ? 'SUCCESS ✓' : 'FAILED ✗') . "</p>";
    
    echo "<p style='color:yellow;margin-top:30px;'>⚠️ DELETE THIS FILE NOW!</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body></html>";
?>
