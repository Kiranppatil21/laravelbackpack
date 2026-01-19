#!/bin/bash

# Laravel Backpack Project Transfer Setup Script
# Run this on the new laptop after cloning/copying the project

echo "🚀 Setting up Laravel Backpack project..."

# Check for required dependencies
echo "🔍 Checking system requirements..."

# Check PHP
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP 8.1+ first."
    exit 1
fi

# Check Composer
if ! command -v composer &> /dev/null; then
    echo "❌ Composer is not installed. Please install Composer first."
    echo "   macOS: brew install composer"
    echo "   Windows: https://getcomposer.org/download/"
    echo "   Linux: curl -sS https://getcomposer.org/installer | php"
    exit 1
fi

# Check Node.js
if ! command -v npm &> /dev/null; then
    echo "❌ Node.js/NPM is not installed. Please install Node.js first."
    exit 1
fi

echo "✅ All required dependencies found!"

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
if composer install; then
    echo "✅ PHP dependencies installed successfully"
else
    echo "❌ Failed to install PHP dependencies"
    echo "   Make sure you have internet connection and Composer is properly installed"
    exit 1
fi

# Verify autoload.php was created
if [ ! -f vendor/autoload.php ]; then
    echo "❌ autoload.php was not created. Running composer dump-autoload..."
    composer dump-autoload
fi

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