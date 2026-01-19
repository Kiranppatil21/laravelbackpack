#!/bin/bash

# Hostinger Deployment Quick Start Script
# This script prepares your Laravel application for Hostinger shared hosting

echo "🚀 Hostinger Deployment Preparation Script"
echo "=========================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Step 1: Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo -e "${RED}Error: artisan file not found. Please run this script from your Laravel project root.${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Laravel project detected${NC}"
echo ""

# Step 2: Clean up development files
echo "📦 Step 1: Cleaning up development files..."
rm -rf node_modules/
rm -rf .git/
rm -rf tests/
rm -f .env
echo -e "${GREEN}✓ Cleanup complete${NC}"
echo ""

# Step 3: Install production dependencies
echo "📦 Step 2: Installing production dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction
if [ $? -ne 0 ]; then
    echo -e "${RED}Error: Composer install failed${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Dependencies installed${NC}"
echo ""

# Step 4: Build frontend assets
echo "🎨 Step 3: Building frontend assets..."
if [ -f "package.json" ]; then
    npm install
    npm run build
    echo -e "${GREEN}✓ Assets built${NC}"
else
    echo -e "${YELLOW}⚠ No package.json found, skipping asset build${NC}"
fi
echo ""

# Step 5: Create production .env template
echo "⚙️  Step 4: Creating .env template..."
cat > .env.production << 'EOF'
APP_NAME="Security Management System"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

RAZORPAY_KEY_ID=
RAZORPAY_KEY_SECRET=
EOF
echo -e "${GREEN}✓ .env.production template created${NC}"
echo ""

# Step 6: Create deployment helper scripts
echo "🛠️  Step 5: Creating deployment helper scripts..."

# Create key generator script
cat > generate_app_key.php << 'EOF'
<?php
/**
 * APP_KEY Generator for Hostinger
 * Upload this file to your Hostinger public_html folder
 * Visit: https://yourdomain.com/generate_app_key.php
 * Copy the generated key to your .env file
 * DELETE THIS FILE after use!
 */

echo "<!DOCTYPE html><html><head><title>Generate APP_KEY</title>";
echo "<style>body{font-family:Arial;padding:40px;background:#f5f5f5;}";
echo ".container{background:white;padding:30px;border-radius:8px;max-width:600px;margin:0 auto;box-shadow:0 2px 10px rgba(0,0,0,0.1);}";
echo "h1{color:#333;}code{background:#f4f4f4;padding:10px;display:block;margin:20px 0;border-radius:4px;word-break:break-all;}";
echo ".warning{background:#fff3cd;padding:15px;border-left:4px solid #ffc107;margin-top:20px;}</style></head><body>";
echo "<div class='container'>";
echo "<h1>🔐 APP_KEY Generator</h1>";
$key = 'base64:' . base64_encode(random_bytes(32));
echo "<p>Your generated APP_KEY:</p>";
echo "<code>APP_KEY=" . htmlspecialchars($key) . "</code>";
echo "<p><strong>Instructions:</strong></p>";
echo "<ol>";
echo "<li>Copy the entire line above (including APP_KEY=)</li>";
echo "<li>Add it to your <code>.env</code> file on the server</li>";
echo "<li><strong style='color:red;'>DELETE THIS FILE immediately after use!</strong></li>";
echo "</ol>";
echo "<div class='warning'><strong>⚠️ Security Warning:</strong> This file should not remain on your server. Delete it immediately after copying the key.</div>";
echo "</div></body></html>";
?>
EOF

# Create migration script
cat > run_migrations.php << 'EOF'
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
EOF

# Create optimization script
cat > optimize_production.php << 'EOF'
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
EOF

# Create permissions script
cat > fix_permissions.php << 'EOF'
<?php
/**
 * Fix Storage Permissions for Hostinger
 * Visit: https://yourdomain.com/fix_permissions.php
 */

echo "<!DOCTYPE html><html><head><title>Fix Permissions</title>";
echo "<style>body{font-family:Arial;padding:20px;}pre{background:#f4f4f4;padding:15px;}</style></head><body>";
echo "<h1>📁 Fix Storage Permissions</h1><pre>";

$directories = [
    'storage',
    'storage/app',
    'storage/app/public',
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
    'bootstrap/cache'
];

foreach ($directories as $dir) {
    if (is_dir(__DIR__ . '/' . $dir)) {
        chmod(__DIR__ . '/' . $dir, 0755);
        echo "✓ {$dir}\n";
    } else {
        echo "✗ {$dir} (not found)\n";
    }
}

// Create storage link
$target = __DIR__ . '/storage/app/public';
$link = __DIR__ . '/public/storage';

if (file_exists($link)) {
    echo "\n✓ Storage link already exists\n";
} else {
    if (symlink($target, $link)) {
        echo "\n✓ Storage link created\n";
    } else {
        echo "\n✗ Failed to create storage link\n";
    }
}

echo "\n✓ Permissions fixed!\n";
echo "</pre></body></html>";
?>
EOF

# Create test database connection script
cat > test_database.php << 'EOF'
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
EOF

echo -e "${GREEN}✓ Helper scripts created:${NC}"
echo "  - generate_app_key.php"
echo "  - run_migrations.php"
echo "  - optimize_production.php"
echo "  - fix_permissions.php"
echo "  - test_database.php"
echo ""

# Step 7: Create .htaccess for public folder redirect
echo "🔒 Step 6: Creating .htaccess files..."
cat > .htaccess << 'EOF'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
EOF

cat > public/.htaccess << 'EOF'
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Security Headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Disable Directory Browsing
Options -Indexes

# Hide sensitive files
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>

<FilesMatch "(composer\.json|composer\.lock|package\.json|\.env|\.git)">
    Order allow,deny
    Deny from all
</FilesMatch>
EOF
echo -e "${GREEN}✓ .htaccess files created${NC}"
echo ""

# Step 8: Create deployment package
echo "📦 Step 7: Creating deployment package..."
DEPLOYMENT_DIR="hostinger_deployment_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$DEPLOYMENT_DIR"

# Copy files
echo "Copying files..."
rsync -av --progress \
    --exclude='node_modules' \
    --exclude='.git' \
    --exclude='tests' \
    --exclude='.env' \
    --exclude='storage/logs/*.log' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='*.md' \
    . "$DEPLOYMENT_DIR/"

# Copy production env template
cp .env.production "$DEPLOYMENT_DIR/.env.example"

echo -e "${GREEN}✓ Deployment package created: ${DEPLOYMENT_DIR}${NC}"
echo ""

# Step 9: Create zip file
echo "📦 Step 8: Creating zip archive..."
cd "$DEPLOYMENT_DIR"
zip -r "../${DEPLOYMENT_DIR}.zip" . -q
cd ..
echo -e "${GREEN}✓ Zip file created: ${DEPLOYMENT_DIR}.zip${NC}"
echo ""

# Final instructions
echo ""
echo "=========================================="
echo -e "${GREEN}✅ Deployment preparation complete!${NC}"
echo "=========================================="
echo ""
echo -e "${YELLOW}📋 Next Steps:${NC}"
echo ""
echo "1. Upload ${DEPLOYMENT_DIR}.zip to Hostinger File Manager"
echo "2. Extract the zip file in public_html directory"
echo "3. Create MySQL database in Hostinger control panel"
echo "4. Upload and run generate_app_key.php to get APP_KEY"
echo "5. Create .env file with your database credentials"
echo "6. Run run_migrations.php to set up database"
echo "7. Run fix_permissions.php to set correct permissions"
echo "8. Run optimize_production.php to cache configs"
echo "9. Test your site at https://yourdomain.com"
echo "10. DELETE all helper PHP files after use!"
echo ""
echo -e "${YELLOW}📖 Full guide: HOSTINGER_DEPLOYMENT_GUIDE.md${NC}"
echo ""
echo -e "${RED}⚠️  Security Reminders:${NC}"
echo "- Delete helper .php files after use"
echo "- Set APP_DEBUG=false in production"
echo "- Use strong database passwords"
echo "- Enable SSL certificate"
echo ""
