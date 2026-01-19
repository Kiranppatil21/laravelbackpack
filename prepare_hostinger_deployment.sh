#!/bin/bash

# Hostinger Shared Hosting Deployment Script
# Run this script to prepare your application for upload

echo "🚀 Preparing SecureServe for Hostinger Deployment..."

# Create deployment directory
mkdir -p hostinger_deployment

# Copy essential files
echo "📁 Copying application files..."
cp -r app hostinger_deployment/
cp -r bootstrap hostinger_deployment/
cp -r config hostinger_deployment/
cp -r database hostinger_deployment/
cp -r public hostinger_deployment/
cp -r resources hostinger_deployment/
cp -r routes hostinger_deployment/
cp -r storage hostinger_deployment/
cp -r vendor hostinger_deployment/

# Copy configuration files
cp composer.json hostinger_deployment/
cp composer.lock hostinger_deployment/
cp package.json hostinger_deployment/
cp package-lock.json hostinger_deployment/
cp artisan hostinger_deployment/
cp .env.hostinger hostinger_deployment/.env.example

# Create required directories
mkdir -p hostinger_deployment/storage/app/public
mkdir -p hostinger_deployment/storage/framework/cache
mkdir -p hostinger_deployment/storage/framework/sessions
mkdir -p hostinger_deployment/storage/framework/testing
mkdir -p hostinger_deployment/storage/framework/views
mkdir -p hostinger_deployment/storage/logs

# Set proper permissions script
echo "📝 Creating permission setup script..."
cat > hostinger_deployment/set_permissions.php << 'EOF'
<?php
// Set proper permissions for Laravel on Hostinger
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
    if (is_dir($dir)) {
        chmod($dir, 0755);
        echo "Set permissions for: $dir\n";
    }
}

// Set file permissions
$files = glob('storage/logs/*.log');
foreach ($files as $file) {
    if (is_file($file)) {
        chmod($file, 0644);
    }
}

echo "Permissions set successfully!\n";
?>
EOF

# Create .htaccess for root directory (if domain points to root)
cat > hostinger_deployment/.htaccess << 'EOF'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
EOF

echo "✅ Deployment package ready in 'hostinger_deployment' directory"
echo ""
echo "📋 Next Steps:"
echo "1. Zip the 'hostinger_deployment' directory"
echo "2. Upload to your Hostinger hosting"
echo "3. Extract in your domain's root directory"
echo "4. Run the post-deployment setup"
echo ""
echo "🔧 See HOSTINGER_DEPLOYMENT_GUIDE.md for detailed instructions"