# Deployment Guide - Security Service SaaS

## Production Deployment Runbook

This guide provides comprehensive instructions for deploying the Security Service SaaS platform to production environments.

---

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Server Setup](#server-setup)
3. [Application Deployment](#application-deployment)
4. [Database Configuration](#database-configuration)
5. [SSL Configuration](#ssl-configuration)
6. [Monitoring Setup](#monitoring-setup)
7. [Backup Configuration](#backup-configuration)
8. [Performance Optimization](#performance-optimization)
9. [Troubleshooting](#troubleshooting)

---

## Prerequisites

### Minimum System Requirements

**Production Server Specifications:**
- **OS**: Ubuntu 20.04 LTS or CentOS 8+
- **RAM**: 8GB minimum, 16GB recommended
- **Storage**: 100GB SSD minimum
- **CPU**: 4 cores minimum, 8 cores recommended
- **Network**: 100 Mbps internet connection

**Required Software:**
- PHP 8.2 or higher
- MySQL 8.0 or higher
- Redis 6.0 or higher
- Node.js 18.x or higher
- Nginx 1.18 or higher
- Supervisor 4.2 or higher
- Let's Encrypt Certbot

### Domain and DNS Setup

1. **Domain Requirements**:
   - Primary domain: `yourdomain.com`
   - Multi-tenant subdomains: `*.yourdomain.com`
   - API subdomain: `api.yourdomain.com` (optional)

2. **DNS Configuration**:
   ```dns
   A Record: yourdomain.com → Server IP
   A Record: *.yourdomain.com → Server IP
   A Record: api.yourdomain.com → Server IP
   ```

---

## Server Setup

### 1. Automated Server Setup

Use the provided deployment script for complete server setup:

```bash
# Download and execute deployment script
curl -o deploy.sh https://raw.githubusercontent.com/yourrepo/scripts/deploy.sh
chmod +x deploy.sh
sudo ./deploy.sh
```

### 2. Manual Server Setup

#### Update System Packages
```bash
# Ubuntu/Debian
sudo apt update && sudo apt upgrade -y

# CentOS/RHEL
sudo dnf update -y
```

#### Install Required Packages
```bash
# Ubuntu/Debian
sudo apt install -y software-properties-common curl wget unzip git

# Add PHP repository
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP and extensions
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-redis \
  php8.2-gd php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip \
  php8.2-bcmath php8.2-intl php8.2-readline php8.2-cli

# Install MySQL
sudo apt install -y mysql-server-8.0

# Install Redis
sudo apt install -y redis-server

# Install Nginx
sudo apt install -y nginx

# Install Supervisor
sudo apt install -y supervisor

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs
```

#### Install Composer
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

### 3. Service Configuration

#### MySQL Configuration
```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Create application database and user
sudo mysql -u root -p << EOF
CREATE DATABASE security_saas;
CREATE USER 'security_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON security_saas.* TO 'security_user'@'localhost';
GRANT CREATE ON *.* TO 'security_user'@'localhost';
FLUSH PRIVILEGES;
EOF
```

#### Redis Configuration
```bash
# Configure Redis
sudo nano /etc/redis/redis.conf

# Key settings to modify:
# maxmemory 2gb
# maxmemory-policy allkeys-lru
# save 900 1
# save 300 10
# save 60 10000

# Restart Redis
sudo systemctl restart redis
sudo systemctl enable redis
```

#### PHP Configuration
```bash
# Configure PHP-FPM
sudo nano /etc/php/8.2/fpm/pool.d/www.conf

# Key settings:
# user = www-data
# group = www-data
# listen = /run/php/php8.2-fpm.sock
# pm = dynamic
# pm.max_children = 50
# pm.start_servers = 5
# pm.min_spare_servers = 5
# pm.max_spare_servers = 35

# Configure PHP settings
sudo nano /etc/php/8.2/fpm/php.ini

# Key settings:
# memory_limit = 256M
# upload_max_filesize = 20M
# post_max_size = 20M
# max_execution_time = 300
# max_input_vars = 3000

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
sudo systemctl enable php8.2-fpm
```

---

## Application Deployment

### 1. Application Code Deployment

#### Clone Repository
```bash
# Create application directory
sudo mkdir -p /var/www/html
cd /var/www/html

# Clone application code
sudo git clone https://github.com/yourusername/security-saas.git
sudo mv security-saas/* ./
sudo mv security-saas/.* ./ 2>/dev/null || true
sudo rmdir security-saas

# Set proper ownership
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
sudo chmod -R 775 storage bootstrap/cache
```

#### Install Dependencies
```bash
# Install Composer dependencies
sudo -u www-data composer install --no-dev --optimize-autoloader

# Install Node.js dependencies
sudo -u www-data npm install --production

# Build frontend assets
sudo -u www-data npm run build
```

### 2. Environment Configuration

#### Create Production Environment File
```bash
# Copy production environment template
sudo cp .env.production .env

# Generate application key
sudo -u www-data php artisan key:generate

# Configure environment variables
sudo nano .env
```

**Critical Environment Variables:**
```env
# Application Configuration
APP_NAME="Security Service SaaS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=security_saas
DB_USERNAME=security_user
DB_PASSWORD=your_secure_password_here

# Cache Configuration
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.yourmailprovider.com
MAIL_PORT=587
MAIL_USERNAME=your_smtp_user
MAIL_PASSWORD=your_smtp_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"

# File Storage
FILESYSTEM_DISK=local

# Payment Gateway
RAZORPAY_KEY=rzp_live_your_key_here
RAZORPAY_SECRET=your_secret_here

# API Configuration
VISITOR_API_KEY=your_secure_api_key_here
VISITOR_HMAC_SECRET=your_hmac_secret_here

# Logging
LOG_CHANNEL=daily
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error
```

### 3. Database Migration and Seeding

```bash
# Run central database migrations
sudo -u www-data php artisan migrate --force

# Seed initial data
sudo -u www-data php artisan db:seed --force

# Create symbolic link for storage
sudo -u www-data php artisan storage:link
```

### 4. Application Optimization

```bash
# Clear all caches
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear

# Optimize for production
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo -u www-data php artisan optimize
```

---

## Database Configuration

### 1. Database Optimization

#### MySQL Configuration for Production
```bash
# Edit MySQL configuration
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf

# Add production optimizations:
[mysqld]
# General optimizations
max_connections = 200
wait_timeout = 600
interactive_timeout = 600

# InnoDB optimizations
innodb_buffer_pool_size = 2G
innodb_log_file_size = 512M
innodb_flush_log_at_trx_commit = 2
innodb_thread_concurrency = 0

# Query cache
query_cache_type = 1
query_cache_size = 256M
query_cache_limit = 1M

# MyISAM optimizations
key_buffer_size = 256M

# Logging
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2

# Restart MySQL
sudo systemctl restart mysql
```

### 2. Database Backup Configuration

#### Automated Daily Backups
```bash
# Create backup script
sudo nano /usr/local/bin/backup-database.sh

#!/bin/bash
# Database backup script

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/mysql"
DB_NAME="security_saas"
DB_USER="security_user"
DB_PASS="your_password_here"

# Create backup directory
mkdir -p $BACKUP_DIR

# Create backup
mysqldump -u$DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/backup_$DB_NAME_$DATE.sql.gz

# Keep only last 30 days of backups
find $BACKUP_DIR -name "backup_$DB_NAME_*.sql.gz" -mtime +30 -delete

echo "Database backup completed: $BACKUP_DIR/backup_$DB_NAME_$DATE.sql.gz"

# Make script executable
sudo chmod +x /usr/local/bin/backup-database.sh

# Add to crontab for daily execution
sudo crontab -e
# Add this line:
0 2 * * * /usr/local/bin/backup-database.sh >> /var/log/mysql-backup.log 2>&1
```

---

## SSL Configuration

### 1. Install SSL Certificate with Let's Encrypt

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Obtain SSL certificate
sudo certbot --nginx -d yourdomain.com -d *.yourdomain.com

# Set up automatic renewal
sudo crontab -e
# Add this line:
0 12 * * * /usr/bin/certbot renew --quiet
```

### 2. Nginx Configuration

#### Main Server Configuration
```bash
# Create Nginx configuration
sudo nano /etc/nginx/sites-available/security-saas

server {
    listen 80;
    server_name yourdomain.com *.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com *.yourdomain.com;
    
    root /var/www/html/public;
    index index.php index.html index.htm;
    
    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
    
    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/javascript application/json image/svg+xml;
    
    # File upload limit
    client_max_body_size 20M;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Security
        fastcgi_hide_header X-Powered-By;
        
        # Timeout settings
        fastcgi_connect_timeout 60;
        fastcgi_send_timeout 180;
        fastcgi_read_timeout 180;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
        fastcgi_temp_file_write_size 256k;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
    
    location /storage {
        expires 1M;
        access_log off;
        add_header Cache-Control "public, immutable";
    }
    
    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg)$ {
        expires 1y;
        access_log off;
        add_header Cache-Control "public, immutable";
    }
}

# Enable site and restart Nginx
sudo ln -s /etc/nginx/sites-available/security-saas /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
sudo systemctl enable nginx
```

---

## Monitoring Setup

### 1. Queue Processing with Supervisor

```bash
# Create Supervisor configuration
sudo nano /etc/supervisor/conf.d/laravel-worker.conf

[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=3
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/worker.log
stopwaitsecs=3600

# Update Supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

### 2. Log Rotation Configuration

```bash
# Create log rotation configuration
sudo nano /etc/logrotate.d/laravel

/var/www/html/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    notifempty
    create 0644 www-data www-data
    postrotate
        sudo supervisorctl restart laravel-worker:*
    endscript
}

# Test log rotation
sudo logrotate -d /etc/logrotate.d/laravel
```

### 3. Application Monitoring

#### Health Check Endpoint
```bash
# Test application health
curl -f https://yourdomain.com/health || echo "Application health check failed"

# Add to monitoring script
sudo nano /usr/local/bin/health-check.sh

#!/bin/bash
# Application health monitoring

URL="https://yourdomain.com/health"
RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" $URL)

if [ $RESPONSE -eq 200 ]; then
    echo "$(date): Application is healthy"
else
    echo "$(date): Application health check failed with HTTP $RESPONSE"
    # Add notification logic here (email, Slack, etc.)
fi

# Make executable and add to cron
sudo chmod +x /usr/local/bin/health-check.sh
sudo crontab -e
# Add this line:
*/5 * * * * /usr/local/bin/health-check.sh >> /var/log/health-check.log 2>&1
```

#### Performance Monitoring Script
```bash
# Create performance monitoring script
sudo nano /usr/local/bin/performance-monitor.sh

#!/bin/bash
# Performance monitoring script

LOG_FILE="/var/log/performance-monitor.log"
TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')

# Check system resources
CPU_USAGE=$(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | cut -d'%' -f1)
MEMORY_USAGE=$(free | grep Mem | awk '{printf("%.2f%%", $3/$2 * 100.0)}')
DISK_USAGE=$(df -h /var/www/html | awk 'NR==2 {print $5}')

# Check queue status
QUEUE_SIZE=$(redis-cli llen "queues:default")

# Check database connections
DB_CONNECTIONS=$(mysql -u security_user -p'your_password' -e "SHOW STATUS WHERE Variable_name = 'Threads_connected';" | awk 'NR==2 {print $2}')

# Log metrics
echo "$TIMESTAMP - CPU: $CPU_USAGE%, Memory: $MEMORY_USAGE, Disk: $DISK_USAGE, Queue: $QUEUE_SIZE jobs, DB Connections: $DB_CONNECTIONS" >> $LOG_FILE

# Alert if thresholds exceeded
if (( $(echo "$CPU_USAGE > 80" | bc -l) )); then
    echo "$TIMESTAMP - HIGH CPU USAGE: $CPU_USAGE%" >> $LOG_FILE
fi

# Make executable and add to cron
sudo chmod +x /usr/local/bin/performance-monitor.sh
sudo crontab -e
# Add this line:
*/10 * * * * /usr/local/bin/performance-monitor.sh
```

---

## Performance Optimization

### 1. PHP-FPM Optimization

```bash
# Optimize PHP-FPM pool configuration
sudo nano /etc/php/8.2/fpm/pool.d/www.conf

[www]
user = www-data
group = www-data

listen = /run/php/php8.2-fpm.sock
listen.owner = www-data
listen.group = www-data

pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 1000

# Process management
pm.process_idle_timeout = 10s
pm.status_path = /status

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

### 2. Redis Optimization

```bash
# Optimize Redis configuration
sudo nano /etc/redis/redis.conf

# Memory management
maxmemory 2gb
maxmemory-policy allkeys-lru

# Persistence
save 900 1
save 300 10
save 60 10000

# Performance
tcp-keepalive 300
timeout 0

# Restart Redis
sudo systemctl restart redis
```

### 3. Database Query Optimization

```sql
-- Create indexes for better performance
-- Run these in MySQL console

USE security_saas;

-- Employee table indexes
CREATE INDEX idx_employees_client_id ON employees(client_id);
CREATE INDEX idx_employees_designation ON employees(designation);
CREATE INDEX idx_employees_joining_date ON employees(joining_date);

-- Attendance logs indexes
CREATE INDEX idx_attendance_employee_date ON attendance_logs(employee_id, check_in_date);
CREATE INDEX idx_attendance_check_in_date ON attendance_logs(check_in_date);

-- Payslips indexes
CREATE INDEX idx_payslips_employee_period ON payslips(employee_id, period_start, period_end);

-- Invoices indexes
CREATE INDEX idx_invoices_client_date ON invoices(client_id, issued_date);
CREATE INDEX idx_invoices_status ON invoices(status);
CREATE INDEX idx_invoices_due_date ON invoices(due_date);
```

---

## Troubleshooting

### Common Deployment Issues

#### 1. Permission Issues
```bash
# Fix file permissions
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
sudo chmod -R 775 storage bootstrap/cache

# Fix SELinux context (if applicable)
sudo setsebool -P httpd_can_network_connect 1
sudo semanage fcontext -a -t httpd_exec_t "/var/www/html/public/index.php"
sudo restorecon -Rv /var/www/html/
```

#### 2. Database Connection Issues
```bash
# Test database connection
mysql -u security_user -p -h 127.0.0.1 security_saas

# Check MySQL service
sudo systemctl status mysql
sudo systemctl restart mysql

# Check MySQL error log
sudo tail -f /var/log/mysql/error.log
```

#### 3. Queue Processing Issues
```bash
# Check Supervisor status
sudo supervisorctl status

# Restart queue workers
sudo supervisorctl restart laravel-worker:*

# Check queue logs
tail -f /var/www/html/storage/logs/worker.log

# Clear failed jobs
cd /var/www/html
sudo -u www-data php artisan queue:failed
sudo -u www-data php artisan queue:flush
```

#### 4. SSL Certificate Issues
```bash
# Check certificate status
sudo certbot certificates

# Renew certificates
sudo certbot renew --dry-run

# Check Nginx configuration
sudo nginx -t

# View SSL logs
sudo tail -f /var/log/letsencrypt/letsencrypt.log
```

#### 5. Performance Issues
```bash
# Check system resources
htop
iostat -x 1
free -h

# Check slow queries
sudo tail -f /var/log/mysql/slow.log

# Monitor PHP-FPM
sudo tail -f /var/log/php8.2-fpm.log

# Check application logs
sudo tail -f /var/www/html/storage/logs/laravel.log
```

### Emergency Procedures

#### Application Recovery
```bash
# Quick application restart
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
sudo supervisorctl restart laravel-worker:*

# Clear all caches
cd /var/www/html
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear

# Rebuild caches
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

#### Database Recovery
```bash
# Restore from backup
gunzip < /var/backups/mysql/backup_security_saas_YYYYMMDD_HHMMSS.sql.gz | mysql -u security_user -p security_saas

# Run migrations after restore
cd /var/www/html
sudo -u www-data php artisan migrate --force
```

### Monitoring Commands

```bash
# System monitoring
watch -n 1 'ps aux --sort=-%cpu | head -20'
watch -n 1 'df -h'
watch -n 1 'free -m'

# Application monitoring
watch -n 5 'curl -s -o /dev/null -w "%{http_code}" https://yourdomain.com'
watch -n 5 'redis-cli llen "queues:default"'

# Database monitoring
watch -n 5 'mysql -u security_user -p"password" -e "SHOW PROCESSLIST;" | wc -l'
```

---

## Post-Deployment Checklist

### Functional Testing
- [ ] Application loads successfully
- [ ] User registration and login work
- [ ] Employee CRUD operations function
- [ ] Attendance check-in/out works
- [ ] Payroll generation executes
- [ ] Invoice creation and payment recording work
- [ ] File uploads process correctly
- [ ] Email notifications send
- [ ] API endpoints respond correctly

### Security Testing
- [ ] SSL certificate is valid and properly configured
- [ ] HTTP redirects to HTTPS
- [ ] Security headers are present
- [ ] Database credentials are secure
- [ ] File permissions are correct
- [ ] API keys are configured
- [ ] Admin access is restricted

### Performance Testing
- [ ] Page load times are acceptable (<2 seconds)
- [ ] Database queries are optimized
- [ ] Caching is working properly
- [ ] Queue processing is functional
- [ ] File uploads handle large files
- [ ] Concurrent user load testing completed

### Monitoring Setup
- [ ] Application health checks are running
- [ ] Error logging is configured
- [ ] Performance monitoring is active
- [ ] Backup procedures are scheduled
- [ ] Alert systems are configured
- [ ] Log rotation is set up

---

*Deployment Guide v1.0 - Last updated: January 2025*

For deployment support, contact: devops@yourcompany.com