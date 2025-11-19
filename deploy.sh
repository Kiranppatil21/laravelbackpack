#!/bin/bash
# Production deployment script for Security Service SaaS
# Usage: ./deploy.sh

set -euo pipefail

echo "🚀 Starting deployment process..."

# Environment Variables Validation
required_vars=(
    "DB_CONNECTION"
    "DB_HOST" 
    "DB_DATABASE"
    "DB_USERNAME"
    "APP_KEY"
    "RAZORPAY_KEY_ID"
    "RAZORPAY_KEY_SECRET"
)

for var in "${required_vars[@]}"; do
    if [[ -z "${!var:-}" ]]; then
        echo "❌ Required environment variable $var is not set"
        exit 1
    fi
done

echo "✅ Environment validation passed"

# Pull latest code
echo "📥 Pulling latest code..."
git pull origin main

# Install dependencies
echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader
npm install --production
npm run build

# Run database migrations
echo "🗄️  Running database migrations..."
php artisan migrate --force

# Clear and optimize caches  
echo "🧹 Optimizing application..."
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache
php artisan view:clear
php artisan view:cache

# Set proper permissions
echo "🔐 Setting permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Restart services
echo "🔄 Restarting services..."
if command -v supervisorctl &> /dev/null; then
    sudo supervisorctl restart laravel-queue:*
fi

if command -v systemctl &> /dev/null; then
    sudo systemctl reload nginx
    sudo systemctl reload php8.2-fpm
fi

echo "✅ Deployment completed successfully!"
echo "🔍 Run: php artisan queue:work --daemon to start queue processing"