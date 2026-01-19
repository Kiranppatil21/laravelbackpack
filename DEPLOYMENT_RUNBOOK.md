# Production Deployment Runbook
# Security Service SaaS - Multi-Tenant Platform

## Pre-Deployment Checklist

### 1. Server Requirements
- [ ] Ubuntu 22.04 LTS or similar
- [ ] PHP 8.2+ with extensions: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML, GD
- [ ] MySQL 8.0+ or MariaDB 10.5+
- [ ] Redis 6.0+
- [ ] Nginx 1.20+
- [ ] Node.js 18+ and NPM
- [ ] Supervisor for queue management
- [ ] SSL certificate (Let's Encrypt recommended)

### 2. Environment Setup
```bash
# Create application user
sudo useradd -m -s /bin/bash securityapp
sudo usermod -aG www-data securityapp

# Clone repository
cd /var/www
sudo git clone https://github.com/yourusername/laravelbackpack.git security-service-saas
sudo chown -R securityapp:www-data security-service-saas
cd security-service-saas

# Set permissions
sudo chmod -R 755 storage
sudo chmod -R 755 bootstrap/cache
sudo chown -R www-data:www-data storage
sudo chown -R www-data:www-data bootstrap/cache
```

### 3. Database Setup
```sql
CREATE DATABASE security_service_saas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'app_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON security_service_saas.* TO 'app_user'@'localhost';
FLUSH PRIVILEGES;
```

### 4. Environment Configuration
```bash
# Copy production environment
cp .env.production .env

# Generate application key
php artisan key:generate

# Update .env with your values:
# - Database credentials
# - Payment gateway keys (Razorpay/Stripe)
# - Mail configuration
# - Security keys (VISITOR_API_KEY, VISITOR_HMAC_SECRET)
```

### 5. Dependencies & Build
```bash
# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node dependencies and build assets
npm ci --production
npm run build
```

### 6. Database Migration
```bash
# Run migrations
php artisan migrate --force

# Seed initial data (roles, demo users)
php artisan db:seed --class=RoleSeeder
```

### 7. Queue Workers Setup
```bash
# Copy supervisor configuration
sudo cp supervisor.conf /etc/supervisor/conf.d/laravel-queue.conf

# Update paths in the config file
sudo nano /etc/supervisor/conf.d/laravel-queue.conf

# Restart supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-queue:*
```

### 8. Web Server Configuration
```bash
# Copy nginx configuration
sudo cp nginx.conf /etc/nginx/sites-available/security-service-saas
sudo ln -s /etc/nginx/sites-available/security-service-saas /etc/nginx/sites-enabled/

# Update domain names in nginx.conf
sudo nano /etc/nginx/sites-available/security-service-saas

# Test and restart nginx
sudo nginx -t
sudo systemctl reload nginx
```

### 9. SSL Certificate
```bash
# Install certbot
sudo apt install certbot python3-certbot-nginx

# Generate certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Test auto-renewal
sudo certbot renew --dry-run
```

### 10. Performance Optimization
```bash
# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Copy MySQL optimizations
sudo cp mysql-production.cnf /etc/mysql/conf.d/
sudo systemctl restart mysql

# Copy Redis optimizations
sudo cp redis-production.conf /etc/redis/
sudo systemctl restart redis
```

## Deployment Process

### Automated Deployment
```bash
# Make deploy script executable
chmod +x deploy.sh

# Run deployment
./deploy.sh
```

### Manual Deployment Steps
```bash
# 1. Pull latest code
git pull origin main

# 2. Install dependencies
composer install --no-dev --optimize-autoloader
npm ci --production && npm run build

# 3. Run migrations
php artisan migrate --force

# 4. Clear caches
php artisan config:clear && php artisan config:cache
php artisan route:clear && php artisan route:cache
php artisan view:clear && php artisan view:cache

# 5. Restart services
sudo supervisorctl restart laravel-queue:*
sudo systemctl reload nginx
```

## Monitoring & Maintenance

### Health Checks
- [ ] Application accessible: `curl https://yourdomain.com/api/health`
- [ ] Queue workers running: `sudo supervisorctl status laravel-queue:*`
- [ ] Database connectivity: `php artisan tinker` → `DB::connection()->getPdo();`
- [ ] Redis connectivity: `redis-cli ping`

### Log Monitoring
```bash
# Application logs
tail -f storage/logs/laravel.log

# Queue worker logs
tail -f storage/logs/worker.log

# Nginx logs
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log

# MySQL logs
tail -f /var/log/mysql/error.log
tail -f /var/log/mysql/mysql-slow.log
```

### Backup Strategy
```bash
# Database backup
mysqldump security_service_saas > backup_$(date +%Y%m%d_%H%M%S).sql

# File uploads backup (if using local storage)
tar -czf uploads_backup_$(date +%Y%m%d_%H%M%S).tar.gz storage/app/public/

# Environment backup
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
```

### Security Updates
```bash
# Regular system updates
sudo apt update && sudo apt upgrade

# PHP/Composer updates
composer update --no-dev

# Node.js updates  
npm update --production && npm run build
```

## Troubleshooting

### Common Issues

1. **500 Internal Server Error**
   - Check Laravel logs: `storage/logs/laravel.log`
   - Verify file permissions: `sudo chown -R www-data:www-data storage`
   - Clear caches: `php artisan config:clear`

2. **Queue Jobs Not Processing**
   - Check supervisor status: `sudo supervisorctl status`
   - Restart workers: `sudo supervisorctl restart laravel-queue:*`
   - Check Redis connection: `redis-cli ping`

3. **Database Connection Errors**
   - Verify credentials in `.env`
   - Test connection: `php artisan tinker` → `DB::connection()->getPdo();`
   - Check MySQL service: `sudo systemctl status mysql`

4. **Payment Gateway Issues**
   - Verify webhook URLs are accessible
   - Check API keys in `.env`
   - Review payment logs in dashboard

### Performance Optimization

1. **Database Optimization**
   - Monitor slow queries: `/var/log/mysql/mysql-slow.log`
   - Add indexes for frequently queried columns
   - Use Redis for session/cache storage

2. **Application Performance**
   - Enable OPcache: `opcache.enable=1` in php.ini
   - Use Laravel Horizon for queue monitoring
   - Implement CDN for static assets

3. **Server Resources**
   - Monitor disk space: `df -h`
   - Check memory usage: `free -m`
   - Monitor CPU: `htop`

## Security Hardening

### Application Security
- [ ] HTTPS enforced with SSL certificate
- [ ] Strong random APP_KEY generated
- [ ] Database credentials secured
- [ ] API keys environment-specific
- [ ] File upload restrictions in place
- [ ] CSRF protection enabled

### Server Security
- [ ] Firewall configured (ufw/iptables)
- [ ] SSH key authentication
- [ ] Regular security updates
- [ ] Fail2ban installed
- [ ] Non-root user for application
- [ ] Database access restricted

## Scaling Considerations

### Horizontal Scaling
- Load balancer for multiple application servers
- Separate database server(s)
- Redis cluster for session storage
- CDN for static assets

### Vertical Scaling
- Increase server resources (CPU/RAM)
- MySQL optimization for larger datasets
- Redis memory allocation
- PHP-FPM worker tuning