# 🚀 Hostinger Shared Hosting Deployment Guide

## 📋 Prerequisites

Before deploying to Hostinger, ensure you have:
- Hostinger shared hosting account with PHP 8.1+ support
- Database access (MySQL/MariaDB)
- FTP/File Manager access
- Domain name configured

## 🔧 Step 1: Prepare Deployment Package

Run the deployment preparation script:
```bash
./prepare_hostinger_deployment.sh
```

This creates a `hostinger_deployment` directory with all necessary files.

## 📦 Step 2: Create Deployment Archive

```bash
cd hostinger_deployment
zip -r ../secureserve_deployment.zip .
```

## 🌐 Step 3: Upload to Hostinger

### Option A: Using File Manager
1. Login to your Hostinger control panel
2. Go to File Manager
3. Navigate to your domain's root directory (usually `public_html`)
4. Upload `secureserve_deployment.zip`
5. Extract the zip file
6. Move all contents from the extracted folder to the root directory

### Option B: Using FTP
1. Connect via FTP client (FileZilla, WinSCP, etc.)
2. Upload the contents of `hostinger_deployment` to your domain root
3. Ensure all files are in the correct location

## 🗄️ Step 4: Database Setup

### Create Database
1. In Hostinger control panel, go to "Databases"
2. Create a new MySQL database
3. Note down:
   - Database name
   - Username
   - Password
   - Host (usually localhost)

### Configure Environment
1. Copy `.env.example` to `.env`
2. Update database configuration:
```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

## 🔐 Step 5: Application Configuration

### Generate Application Key
Create a PHP file `generate_key.php` in your root directory:
```php
<?php
require_once 'vendor/autoload.php';
$key = 'base64:' . base64_encode(random_bytes(32));
echo "APP_KEY=" . $key . "\n";
?>
```

Run it once via browser, copy the key to your `.env` file, then delete the file.

### Update Environment Variables
Edit your `.env` file with production values:

```env
APP_NAME=SecureServe
APP_ENV=production
APP_KEY=base64:your_generated_key_here
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database (from Step 4)
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_hostinger_database
DB_USERNAME=your_hostinger_user
DB_PASSWORD=your_hostinger_password

# Email Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com

# Razorpay (for payments)
RAZORPAY_KEY_ID=your_razorpay_key
RAZORPAY_KEY_SECRET=your_razorpay_secret
```

## 📁 Step 6: Directory Structure & Permissions

Your Hostinger directory should look like this:
```
public_html/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
├── routes/
├── storage/
├── vendor/
├── .env
├── artisan
├── composer.json
└── .htaccess
```

### Set Permissions
Create and run `set_permissions.php` in your root directory:
```php
<?php
$directories = ['storage', 'bootstrap/cache'];
foreach ($directories as $dir) {
    if (is_dir($dir)) {
        chmod($dir, 0755);
        echo "Set permissions for: $dir\n";
    }
}
?>
```

## 🗃️ Step 7: Database Migration

Create `migrate.php` in your root directory:
```php
<?php
require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: __DIR__)
    ->withRouting(
        web: __DIR__.'/routes/web.php',
        api: __DIR__.'/routes/api.php',
        commands: __DIR__.'/routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Run migrations
echo "Running migrations...\n";
$kernel->call('migrate', ['--force' => true]);

// Seed basic data
echo "Seeding database...\n";
$kernel->call('db:seed', ['--force' => true]);

echo "Database setup complete!\n";
?>
```

Run this once via browser, then delete the file.

## 🔧 Step 8: Configure Domain

### If domain points to root directory:
Add this `.htaccess` in your root:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

### If you can point domain to public directory:
Point your domain directly to the `public` folder for better security.

## 📧 Step 9: Email Configuration

### Using Hostinger SMTP:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
```

Create the email account in Hostinger control panel first.

## 🎯 Step 10: Test Deployment

1. Visit your website: `https://yourdomain.com`
2. Test marketing pages:
   - Homepage: `/`
   - Features: `/features`
   - Pricing: `/pricing`
   - Documentation: `/documentation`
   - Help Center: `/help-center`
3. Test admin panel: `/admin`
4. Test registration: `/register`

## 🛠️ Troubleshooting

### Common Issues:

**500 Internal Server Error:**
- Check `.env` file exists and has correct APP_KEY
- Verify database credentials
- Check storage permissions (755)

**Assets not loading:**
- Ensure `public/build` directory exists
- Check APP_URL in .env matches your domain
- Verify .htaccess is working

**Database connection issues:**
- Double-check database credentials in .env
- Ensure database exists in Hostinger panel
- Test database connection manually

**Permission Issues:**
```bash
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

### Maintenance Commands

Create `maintenance.php` for ongoing maintenance:
```php
<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Clear all caches
$kernel->call('cache:clear');
$kernel->call('config:clear');
$kernel->call('route:clear');
$kernel->call('view:clear');

echo "Caches cleared!\n";
?>
```

## 🔒 Security Checklist

- [ ] APP_DEBUG=false in production
- [ ] Strong APP_KEY generated
- [ ] Database credentials secure
- [ ] .env file not publicly accessible
- [ ] Storage directories have correct permissions
- [ ] Maintenance files removed after use

## 📞 Support

If you encounter issues:
1. Check Hostinger documentation
2. Review Laravel deployment guides
3. Contact Hostinger support for server-specific issues

---

Your SecureServe application is now ready for Hostinger shared hosting! 🎉