# 🛠️ Transfer Troubleshooting Guide

## Common Issues and Solutions

### 1. "autoload.php: No such file or directory"

**Cause**: Missing vendor dependencies
**Solution**:
```bash
# Install Composer dependencies first
composer install

# Verify autoload.php exists
ls -la vendor/autoload.php

# If still missing, regenerate autoloader
composer dump-autoload
```

### 2. "composer: command not found"

**Cause**: Composer not installed
**Solution**:

**macOS**:
```bash
brew install composer
```

**Windows**:
- Download from: https://getcomposer.org/download/
- Run the installer

**Linux**:
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 3. "php: command not found"

**Cause**: PHP not installed
**Solution**:

**macOS**:
```bash
brew install php
```

**Windows**:
- Download from: https://windows.php.net/download/
- Or use XAMPP: https://www.apachefriends.org/

**Linux (Ubuntu)**:
```bash
sudo apt update
sudo apt install php8.1 php8.1-cli php8.1-sqlite3 php8.1-mbstring
```

### 4. Database Connection Issues

**Cause**: SQLite database file missing or permissions
**Solution**:
```bash
# Create database file
touch database/database.sqlite

# Set permissions (Unix/macOS)
chmod 664 database/database.sqlite
chmod 755 database/

# Run migrations
php artisan migrate
```

### 5. "Permission denied" Errors

**Cause**: Incorrect file permissions
**Solution**:
```bash
# Fix Laravel permissions (Unix/macOS)
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chmod 664 database/database.sqlite
```

### 6. "Class not found" Errors

**Cause**: Autoloader cache issues
**Solution**:
```bash
# Clear and regenerate autoloader
composer dump-autoload
php artisan clear-compiled
php artisan config:clear
```

### 7. "npm: command not found"

**Cause**: Node.js not installed
**Solution**:
- Download from: https://nodejs.org/
- Or use package manager:
  ```bash
  # macOS
  brew install node
  
  # Windows (with Chocolatey)
  choco install nodejs
  
  # Linux
  sudo apt install nodejs npm
  ```

### 8. Frontend Assets Not Loading

**Cause**: Assets not built
**Solution**:
```bash
# Install NPM dependencies
npm install

# Build assets
npm run build

# Or for development with watching
npm run dev
```

### 9. "Key too long" Database Error

**Cause**: MySQL key length limits
**Solution**: This project uses SQLite, but if you switch to MySQL:
```bash
# In AppServiceProvider.php boot method
Schema::defaultStringLength(191);
```

### 10. Environment File Issues

**Cause**: Missing or incorrect .env file
**Solution**:
```bash
# Copy example file
cp .env.example .env

# Generate app key
php artisan key:generate

# Edit database settings in .env:
DB_CONNECTION=sqlite
```

## 🚀 Quick Reset Commands

If all else fails, try this complete reset:

```bash
# 1. Clean up
rm -rf vendor/ node_modules/ bootstrap/cache/*.php

# 2. Reinstall everything
composer install
npm install

# 3. Reset environment
cp .env.example .env
php artisan key:generate

# 4. Reset database
rm database/database.sqlite
touch database/database.sqlite
php artisan migrate --force
php artisan db:seed --force

# 5. Build assets
npm run build

# 6. Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## 📞 Getting Help

If you're still having issues:

1. **Check Laravel logs**: `storage/logs/laravel.log`
2. **Enable debug mode**: Set `APP_DEBUG=true` in `.env`
3. **Check PHP version**: `php --version` (needs 8.1+)
4. **Verify extensions**: `php -m | grep -E "(sqlite|pdo|mbstring)"`

## 💡 Pro Tips

- Always run `composer install` before `php artisan` commands
- Use `php artisan serve --host=0.0.0.0` to access from other devices
- Run `npm run dev` in background for auto-compilation during development
- Use `php artisan tinker` to test database connections