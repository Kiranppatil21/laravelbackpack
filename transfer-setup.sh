#!/bin/bash

# Laravel Backpack Project Transfer Setup Script
# Run this on the new laptop after cloning/copying the project

echo "🚀 Setting up Laravel Backpack project..."

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install

# Install Node.js dependencies  
echo "📦 Installing Node.js dependencies..."
npm install

# Copy environment file if not exists
if [ ! -f .env ]; then
    echo "📝 Creating environment file..."
    cp .env.example .env
fi

# Generate application key
echo "🔐 Generating application key..."
php artisan key:generate

# Create SQLite database if not exists
if [ ! -f database/database.sqlite ]; then
    echo "🗄️ Creating SQLite database..."
    touch database/database.sqlite
fi

# Clear and rebuild caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Run migrations (only if database is empty)
echo "📊 Setting up database..."
php artisan migrate --force
php artisan db:seed --force

# Build frontend assets
echo "🎨 Building frontend assets..."
npm run build

# Set proper permissions (Unix/Linux/macOS)
echo "🔒 Setting permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

echo "✅ Setup complete!"
echo "🌐 Run 'php artisan serve' to start the development server"
echo "🔧 Access admin panel at: http://localhost:8000/admin"
echo "🔑 Admin credentials: admin@example.com / password123"