# 🚀 Hostinger Deployment Checklist

Use this checklist to ensure a smooth deployment of your Laravel application to Hostinger shared hosting.

---

## 📋 Pre-Deployment (Local Machine)

### Preparation
- [ ] Backed up current database
- [ ] Tested application locally
- [ ] All migrations working correctly
- [ ] Frontend assets built successfully (`npm run build`)
- [ ] Production dependencies installed (`composer install --no-dev`)

### Run Deployment Script
```bash
./deploy_to_hostinger.sh
```

### After Script Completes
- [ ] `hostinger_deployment_YYYYMMDD_HHMMSS.zip` file created
- [ ] Helper PHP scripts generated
- [ ] `.env.production` template created

---

## 🌐 Hostinger Control Panel Setup

### Database Setup
- [ ] Logged into Hostinger hPanel
- [ ] Created new MySQL database
- [ ] Created database user
- [ ] Granted all privileges to user
- [ ] Noted down credentials:
  ```
  DB_HOST: localhost
  DB_PORT: 3306
  DB_DATABASE: ________________
  DB_USERNAME: ________________
  DB_PASSWORD: ________________
  ```

### Email Setup (Optional but Recommended)
- [ ] Created email account (e.g., noreply@yourdomain.com)
- [ ] Noted email password: ________________
- [ ] SMTP: smtp.hostinger.com
- [ ] Port: 587
- [ ] Encryption: TLS

### SSL Certificate
- [ ] Enabled free Let's Encrypt SSL
- [ ] Certificate status: Active
- [ ] Force HTTPS enabled

---

## 📤 File Upload

### Upload via File Manager
- [ ] Opened File Manager in hPanel
- [ ] Navigated to `public_html` directory
- [ ] Uploaded `hostinger_deployment_XXXXXXX.zip`
- [ ] Extracted zip file
- [ ] Moved all contents to root (if in subdirectory)
- [ ] Deleted zip file after extraction

### Verify Directory Structure
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
├── .htaccess
├── artisan
├── composer.json
└── [helper PHP files]
```
- [ ] All Laravel directories present
- [ ] `.htaccess` in root exists
- [ ] `public` folder exists with index.php

---

## ⚙️ Configuration Steps

### 1. Generate Application Key
- [ ] Navigate to: `https://yourdomain.com/generate_app_key.php`
- [ ] Copied generated APP_KEY
- [ ] Saved APP_KEY for next step
- [ ] **DELETED generate_app_key.php file**

### 2. Create .env File
- [ ] Created `.env` file in `public_html/`
- [ ] Copied from `.env.example` or `.env.production`
- [ ] Updated configuration:

```env
APP_NAME="Your App Name"
APP_ENV=production
APP_KEY=base64:XXXXXXXXXX  ← Paste generated key
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database    ← From Hostinger
DB_USERNAME=your_user        ← From Hostinger
DB_PASSWORD=your_password    ← From Hostinger

MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
```

- [ ] APP_KEY pasted
- [ ] Database credentials updated
- [ ] APP_URL set to your domain
- [ ] APP_DEBUG set to `false`
- [ ] APP_ENV set to `production`

### 3. Test Database Connection
- [ ] Navigate to: `https://yourdomain.com/test_database.php`
- [ ] Connection successful (green message)
- [ ] Tables count shown
- [ ] **DELETED test_database.php file**

If connection failed:
- [ ] Double-checked database credentials
- [ ] Verified database exists in Hostinger
- [ ] Checked DB_HOST is `localhost`

---

## 🗄️ Database Setup

### Run Migrations
- [ ] Navigate to: `https://yourdomain.com/run_migrations.php`
- [ ] Migrations completed successfully
- [ ] Role seeder completed successfully
- [ ] **DELETED run_migrations.php file**

If migrations failed:
- [ ] Checked error message
- [ ] Verified database connection works
- [ ] Ensured database is empty or backed up

---

## 🔧 File Permissions & Storage

### Fix Permissions
- [ ] Navigate to: `https://yourdomain.com/fix_permissions.php`
- [ ] All directories show checkmarks
- [ ] Storage link created successfully
- [ ] Can keep this file for future use (optional)

### Manual Verification (via File Manager)
- [ ] `storage/` - 755
- [ ] `storage/app/` - 755
- [ ] `storage/app/public/` - 755
- [ ] `storage/framework/` - 755
- [ ] `storage/logs/` - 755
- [ ] `bootstrap/cache/` - 755

---

## ⚡ Optimization

### Run Optimization
- [ ] Navigate to: `https://yourdomain.com/optimize_production.php`
- [ ] Caches cleared successfully
- [ ] Production caches built successfully
- [ ] Can keep this file for future updates

---

## 🧪 Testing

### Basic Functionality
- [ ] Homepage loads: `https://yourdomain.com`
- [ ] No 500 errors displayed
- [ ] CSS and JS loading correctly
- [ ] Images displaying properly

### Admin Panel
- [ ] Admin login page: `https://yourdomain.com/admin`
- [ ] Can log in with default credentials
- [ ] Dashboard loads correctly
- [ ] Menu items visible

### User Registration
- [ ] Registration page: `https://yourdomain.com/register`
- [ ] Can create new account
- [ ] Email verification (if enabled)
- [ ] Can log in after registration

### Database Operations
- [ ] Can create records (employees, clients, etc.)
- [ ] Can edit records
- [ ] Can delete records
- [ ] Can view reports

### File Uploads
- [ ] Can upload images
- [ ] Uploaded files accessible
- [ ] Storage link working

### Email (if configured)
- [ ] Test email sent successfully
- [ ] Email received in inbox
- [ ] Email formatting correct

---

## 🔒 Security Hardening

### Remove Development Files
- [ ] **DELETED generate_app_key.php**
- [ ] **DELETED run_migrations.php**
- [ ] **DELETED test_database.php**
- [ ] Verified no `.env.example` in public_html
- [ ] Verified no `.git` directory

### Verify Settings
- [ ] APP_DEBUG=false in .env
- [ ] APP_ENV=production in .env
- [ ] Error display disabled
- [ ] Directory browsing disabled

### Access Control
- [ ] `.htaccess` protecting sensitive files
- [ ] Storage directory not directly accessible
- [ ] `.env` file not downloadable

### SSL Certificate
- [ ] HTTPS working
- [ ] HTTP redirects to HTTPS
- [ ] Mixed content warnings resolved
- [ ] Green padlock in browser

---

## 🔄 Cron Jobs (Optional but Recommended)

### Access Cron Jobs
- [ ] hPanel → Advanced → Cron Jobs

### Add Laravel Scheduler
```
* * * * * cd /home/username/public_html && php artisan schedule:run >> /dev/null 2>&1
```
- [ ] Cron job added
- [ ] Username replaced with actual Hostinger username

### Add Queue Worker (if using queues)
```
* * * * * cd /home/username/public_html && php artisan queue:work --stop-when-empty >> /dev/null 2>&1
```
- [ ] Queue worker cron added (if needed)

---

## 📧 Email Configuration Testing

### Send Test Email
Access `tinker` via SSH or create test script:
```php
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

Mail::raw('Test email from production', function($message) {
    $message->to('your@email.com')
            ->subject('Production Test');
});

echo "Email sent!";
?>
```

- [ ] Test email created
- [ ] Email sent successfully
- [ ] Email received
- [ ] **DELETED test email script**

---

## 🛡️ Backup Setup

### Enable Automatic Backups
- [ ] hPanel → Backups
- [ ] Automatic backups enabled
- [ ] Backup frequency set
- [ ] Backup retention configured

### Manual Backup
- [ ] Created initial manual backup
- [ ] Backup includes files
- [ ] Backup includes database
- [ ] Backup download tested

---

## 📊 Performance Optimization

### Check Performance
- [ ] Page load time < 3 seconds
- [ ] Images optimized
- [ ] Assets cached properly
- [ ] Database queries optimized

### Optional Enhancements
- [ ] Enabled OPcache (contact Hostinger support)
- [ ] Configured CDN (Cloudflare)
- [ ] Database indexes optimized
- [ ] Lazy loading implemented

---

## 📱 Final Verification

### Cross-Browser Testing
- [ ] Chrome ✓
- [ ] Firefox ✓
- [ ] Safari ✓
- [ ] Edge ✓
- [ ] Mobile browsers ✓

### Mobile Responsiveness
- [ ] Homepage responsive
- [ ] Admin panel usable on mobile
- [ ] Forms work on mobile
- [ ] Navigation accessible

### SEO Basics
- [ ] Sitemap accessible
- [ ] Robots.txt configured
- [ ] Meta tags present
- [ ] Analytics installed (if needed)

---

## 🎯 Post-Deployment Tasks

### Documentation
- [ ] Updated README with production URL
- [ ] Documented admin credentials
- [ ] Documented database credentials
- [ ] Created maintenance procedures

### Monitoring Setup
- [ ] Set up uptime monitoring
- [ ] Configured error logging
- [ ] Set up analytics
- [ ] Created alert system

### Team Communication
- [ ] Notified team of deployment
- [ ] Shared admin credentials securely
- [ ] Documented known issues
- [ ] Scheduled training session

---

## ✅ Deployment Complete!

### Final Checklist
- [ ] Application accessible at production URL
- [ ] All features working correctly
- [ ] Security measures in place
- [ ] Backups configured
- [ ] Monitoring active
- [ ] Team notified
- [ ] Documentation updated

### Important Files to Keep Safe
1. `.env` file (contains sensitive data)
2. Database credentials
3. Email credentials
4. Razorpay API keys
5. Admin login credentials

### Files to Keep on Server (Optional)
- `optimize_production.php` - Run after updates
- `fix_permissions.php` - Run if permission issues occur

### Files to Delete from Server
- ✓ `generate_app_key.php`
- ✓ `run_migrations.php`
- ✓ `test_database.php`
- ✓ Any other test scripts

---

## 🆘 Troubleshooting Quick Reference

### 500 Internal Server Error
1. Check `.env` file exists
2. Verify APP_KEY is set
3. Check storage permissions (755)
4. Review error logs: `storage/logs/laravel.log`

### Database Connection Failed
1. Verify credentials in `.env`
2. Check database exists in Hostinger
3. Ensure DB_HOST=localhost
4. Test with `test_database.php` (then delete)

### Assets Not Loading
1. Run `optimize_production.php`
2. Check APP_URL in `.env`
3. Clear browser cache
4. Verify `public/build/` exists

### Permission Denied Errors
1. Run `fix_permissions.php`
2. Set storage folders to 755
3. Check file ownership

### Email Not Sending
1. Verify SMTP credentials
2. Check email account exists in Hostinger
3. Test with simple script
4. Review mail logs

---

## 📞 Support Contacts

- **Hostinger Support**: https://support.hostinger.com
- **Laravel Documentation**: https://laravel.com/docs
- **Backpack Documentation**: https://backpackforlaravel.com/docs

---

**Congratulations! Your Laravel application is now live on Hostinger! 🎉**

Last Updated: December 12, 2025
