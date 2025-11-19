<?php
// Emergency debugging script for Hostinger deployment issues
// Upload this to public_html and visit in browser to diagnose problems

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>🔍 Hostinger Deployment Diagnostics</h2>";
echo "<hr>";

// 1. Check if basic PHP is working
echo "<h3>1. PHP Status</h3>";
echo "<p>✅ PHP Version: " . phpversion() . "</p>";
echo "<p>✅ Server: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";

// 2. Check directory structure
echo "<h3>2. Directory Structure</h3>";
$requiredDirs = ['app', 'bootstrap', 'config', 'database', 'public', 'resources', 'routes', 'storage', 'vendor'];
foreach ($requiredDirs as $dir) {
    if (is_dir($dir)) {
        echo "<p>✅ {$dir}/ exists</p>";
    } else {
        echo "<p>❌ {$dir}/ missing</p>";
    }
}

// 3. Check critical files
echo "<h3>3. Critical Files</h3>";
$requiredFiles = ['.env', 'artisan', 'composer.json', '.htaccess', 'vendor/autoload.php'];
foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "<p>✅ {$file} exists</p>";
    } else {
        echo "<p>❌ {$file} missing</p>";
    }
}

// 4. Check .env file
echo "<h3>4. Environment Configuration</h3>";
if (file_exists('.env')) {
    $envContent = file_get_contents('.env');
    if (strpos($envContent, 'APP_KEY=') !== false) {
        echo "<p>✅ APP_KEY found in .env</p>";
    } else {
        echo "<p>❌ APP_KEY missing in .env</p>";
    }
    
    if (strpos($envContent, 'DB_DATABASE=') !== false) {
        echo "<p>✅ Database config found</p>";
    } else {
        echo "<p>❌ Database config missing</p>";
    }
} else {
    echo "<p>❌ .env file missing</p>";
}

// 5. Check storage permissions
echo "<h3>5. Storage Permissions</h3>";
$storageDirs = ['storage', 'storage/logs', 'storage/framework/cache', 'bootstrap/cache'];
foreach ($storageDirs as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "<p>✅ {$dir} is writable</p>";
        } else {
            echo "<p>❌ {$dir} not writable (permissions issue)</p>";
        }
    } else {
        echo "<p>❌ {$dir} doesn't exist</p>";
    }
}

// 6. Test Laravel autoloader
echo "<h3>6. Laravel Framework</h3>";
try {
    if (file_exists('vendor/autoload.php')) {
        require_once 'vendor/autoload.php';
        echo "<p>✅ Composer autoloader works</p>";
        
        if (file_exists('bootstrap/app.php')) {
            $app = require_once 'bootstrap/app.php';
            echo "<p>✅ Laravel application bootstrap works</p>";
        } else {
            echo "<p>❌ bootstrap/app.php missing</p>";
        }
    } else {
        echo "<p>❌ Composer autoloader missing</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Laravel bootstrap error: " . $e->getMessage() . "</p>";
}

// 7. Database connection test
echo "<h3>7. Database Connection</h3>";
if (file_exists('.env')) {
    $envVars = parse_ini_file('.env');
    if (isset($envVars['DB_HOST']) && isset($envVars['DB_DATABASE'])) {
        try {
            $pdo = new PDO(
                "mysql:host={$envVars['DB_HOST']};dbname={$envVars['DB_DATABASE']}", 
                $envVars['DB_USERNAME'], 
                $envVars['DB_PASSWORD']
            );
            echo "<p>✅ Database connection successful</p>";
        } catch (Exception $e) {
            echo "<p>❌ Database connection failed: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>❌ Database configuration incomplete</p>";
    }
} else {
    echo "<p>❌ Cannot test database - .env missing</p>";
}

echo "<hr>";
echo "<h3>🔧 Next Steps</h3>";
echo "<p>1. Fix any ❌ issues shown above</p>";
echo "<p>2. Delete this debug file after fixing issues</p>";
echo "<p>3. Contact me with specific error details if needed</p>";

echo "<hr>";
echo "<p><strong>⚠️ Security Notice:</strong> Delete this file immediately after debugging!</p>";
?>