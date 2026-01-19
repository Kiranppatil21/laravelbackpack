<?php
/**
 * Test Database Connection
 * Visit: https://yourdomain.com/test_database.php
 * DELETE after confirming connection works
 */

echo "<!DOCTYPE html><html><head><title>Test Database</title>";
echo "<style>body{font-family:Arial;padding:20px;}.success{color:green;}.error{color:red;}</style></head><body>";
echo "<h1>🗄️ Database Connection Test</h1>";

try {
    require __DIR__.'/vendor/autoload.php';
    $app = require_once __DIR__.'/bootstrap/app.php';
    
    $pdo = $app->make('db')->connection()->getPdo();
    echo "<p class='success'>✓ Database connected successfully!</p>";
    echo "<p>Driver: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "</p>";
    echo "<p>Server: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "</p>";
    
    // Test query
    $tables = $app->make('db')->select('SHOW TABLES');
    echo "<p>Tables found: " . count($tables) . "</p>";
    
    echo "<p style='color:orange;'>⚠️ DELETE THIS FILE after confirming!</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body></html>";
?>
