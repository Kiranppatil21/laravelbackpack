<?php
// One-time database migration script for Hostinger
// Upload this to public_html, run once in browser, then delete

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>SecureServe Database Migration</h2>";

try {
    require_once 'vendor/autoload.php';
    
    // Bootstrap Laravel application
    $app = require_once 'bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    
    echo "<p>✅ Laravel application loaded</p>";
    
    // Test database connection
    echo "<p>🔍 Testing database connection...</p>";
    $kernel->call('migrate:status');
    echo "<p>✅ Database connected successfully</p>";
    
    // Run migrations
    echo "<p>🔄 Running migrations...</p>";
    $exitCode = $kernel->call('migrate', ['--force' => true]);
    
    if ($exitCode === 0) {
        echo "<p>✅ Migrations completed successfully</p>";
        
        // Run seeders
        echo "<p>🌱 Seeding initial data...</p>";
        $seedCode = $kernel->call('db:seed', ['--force' => true]);
        
        if ($seedCode === 0) {
            echo "<p>✅ Database seeding completed</p>";
            echo "<h3>🎉 Database setup successful!</h3>";
            echo "<p><strong>⚠️ Important:</strong> Delete this file immediately!</p>";
        } else {
            echo "<p>⚠️ Warning: Seeding had some issues, but migration completed</p>";
        }
    } else {
        echo "<p>❌ Migration failed</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Check your .env database configuration</p>";
}
?>