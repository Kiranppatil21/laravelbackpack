<?php
/**
 * Production Optimization Script for Hostinger
 * Run this after deployment to cache configurations
 * Visit: https://yourdomain.com/optimize_production.php
 * Can be kept and run periodically after updates
 */

echo "<!DOCTYPE html><html><head><title>Optimize Application</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#f8f9fa;}pre{background:#fff;padding:15px;border-radius:5px;border:1px solid #ddd;}</style></head><body>";
echo "<h1>⚡ Application Optimization</h1><pre>";

try {
    require __DIR__.'/vendor/autoload.php';
    $app = require_once __DIR__.'/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    
    echo "Clearing caches...\n";
    $kernel->call('config:clear');
    $kernel->call('route:clear');
    $kernel->call('view:clear');
    $kernel->call('cache:clear');
    echo "✓ Caches cleared\n\n";
    
    echo "Building production caches...\n";
    $kernel->call('config:cache');
    $kernel->call('route:cache');
    $kernel->call('view:cache');
    $kernel->call('event:cache');
    echo "✓ Caches built\n\n";
    
    echo "✓ Optimization complete!\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "</pre></body></html>";
?>
