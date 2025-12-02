# Enhanced Visitor Management System - Setup and Run Guide

## 🚀 Quick Start Guide

This guide will help you set up and run the Enhanced Visitor Management System. Follow these steps in order for a complete setup.

## 📋 Prerequisites

Before starting, ensure you have:
- **PHP 8.1+** with required extensions
- **Composer** for dependency management
- **Node.js & NPM** for frontend assets
- **Database** (MySQL/PostgreSQL recommended for production, SQLite for development)
- **Web Server** (Apache/Nginx for production, Laravel dev server for development)

## ⚙️ Environment Setup

### 1. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### 2. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Configure Environment Variables

Edit your `.env` file with the following essential configurations:

```env
# Application Settings
APP_NAME="Enhanced Visitor Management"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration (Choose one)
# For Development (SQLite)
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite

# For Production (MySQL)
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=visitor_management
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# Cache and Session
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=noreply@yourcompany.com

# Push Notifications (Firebase)
FIREBASE_SERVER_KEY=your_firebase_server_key

# Visitor API Configuration
VISITOR_API_KEY=your_secure_api_key
VISITOR_HMAC_SECRET=your_hmac_secret_key

# Background Check Service (Optional)
BACKGROUND_CHECK_PROVIDER=your_provider
BACKGROUND_CHECK_API_KEY=your_api_key

# File Storage
FILESYSTEM_DISK=local
# For production, consider using AWS S3
# AWS_ACCESS_KEY_ID=your_access_key
# AWS_SECRET_ACCESS_KEY=your_secret_key
# AWS_DEFAULT_REGION=us-east-1
# AWS_BUCKET=your_bucket_name
```

## 🗄️ Database Setup

### 1. Create Database (if using MySQL/PostgreSQL)

```bash
# MySQL
mysql -u root -p -e "CREATE DATABASE visitor_management;"

# PostgreSQL
createdb visitor_management
```

### 2. Run Migrations

```bash
# Run central database migrations
php artisan migrate

# Seed the database with default roles and permissions
php artisan db:seed
```

### 3. Setup Tenant Database (Multi-tenancy)

```bash
# Create a test tenant
php artisan tenants:create your-domain.com

# Run tenant-specific migrations
php artisan tenants:migrate
```

## 🔧 Additional Configuration

### 1. Storage Setup

```bash
# Create storage link for public file access
php artisan storage:link

# Create visitor photo directories
mkdir -p storage/app/public/visitors/photos
mkdir -p storage/app/public/visitors/documents
```

### 2. Queue Configuration

For production, set up queue workers:

```bash
# Start queue worker (keep running in background)
php artisan queue:work --daemon

# For development, you can process jobs synchronously
# Set QUEUE_CONNECTION=sync in .env
```

### 3. Schedule Setup (Production)

Add to your crontab for automated tasks:

```bash
# Edit crontab
crontab -e

# Add this line
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

## 🏃‍♂️ Running the Application

### Development Mode

```bash
# Start the Laravel development server
php artisan serve

# In another terminal, compile frontend assets
npm run dev

# Optional: Start queue worker for background jobs
php artisan queue:work
```

Visit: http://localhost:8000

### Production Deployment

```bash
# Optimize for production
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Build production assets
npm run build

# Set proper permissions
chmod -R 755 storage bootstrap/cache
```

## 🎯 System Features Access

### Admin Panel
- URL: `http://localhost:8000/admin`
- Default credentials: Check `database/seeders/UserSeeder.php`

### API Endpoints

#### Visitor Management
```
GET    /api/visitors              # List visitors
POST   /api/visitors              # Create visitor
GET    /api/visitors/{id}         # Get visitor details
PUT    /api/visitors/{id}         # Update visitor
DELETE /api/visitors/{id}         # Delete visitor
```

#### Mobile App Integration
```
POST   /api/mobile/register-device      # Register mobile device
POST   /api/mobile/generate-qr          # Generate QR code
POST   /api/mobile/scan-qr              # Process QR scan
POST   /api/mobile/upload-photo         # Upload visitor photo
GET    /api/mobile/visitor-status       # Get visitor status
POST   /api/mobile/checkout             # Check out visitor
```

#### IoT Device Management
```
GET    /api/devices                     # List IoT devices
POST   /api/devices                     # Register device
POST   /api/iot/heartbeat              # Device heartbeat
POST   /api/iot/checkin                # Device check-in
POST   /api/iot/checkout               # Device check-out
```

#### Analytics & Reporting
```
GET    /api/visitor-analytics/dashboard         # Real-time dashboard
GET    /api/visitor-analytics/compliance-report # Compliance reports
POST   /api/visitor-analytics/export-visitor-data # Export data
```

### Security Features
```
GET    /api/visitor-security/watchlist          # Manage watchlist
POST   /api/visitor-security/background-check   # Trigger background check
GET    /api/visitor-security/alerts             # Security alerts
POST   /api/visitor-security/approve-visitor    # Approve visitor
```

## 📱 Mobile App Setup

### Firebase Configuration

1. Create a Firebase project
2. Add your Firebase server key to `.env`
3. Configure push notification settings

### QR Code Testing

```bash
# Test QR code generation
curl -X POST http://localhost:8000/api/mobile/generate-qr \
  -H "Content-Type: application/json" \
  -d '{"visitor_code": "VIS001"}'
```

## 🔧 Testing the System

### Run Test Suite

```bash
# Run all tests
php artisan test

# Run specific feature tests
php artisan test --filter VisitorManagementTest

# Run with coverage
php artisan test --coverage
```

### Manual Testing

1. **Create a Visitor**:
   ```bash
   curl -X POST http://localhost:8000/api/visitors \
     -H "Content-Type: application/json" \
     -d '{
       "name": "John Doe",
       "phone": "+1234567890",
       "email": "john@example.com",
       "company": "Test Company",
       "purpose_of_visit": "meeting"
     }'
   ```

2. **Generate QR Code**:
   ```bash
   curl -X POST http://localhost:8000/api/mobile/generate-qr \
     -H "Content-Type: application/json" \
     -d '{"visitor_code": "returned_visitor_code"}'
   ```

3. **Check-in via QR Scan**:
   ```bash
   curl -X POST http://localhost:8000/api/mobile/scan-qr \
     -H "Content-Type: application/json" \
     -d '{"qr_data": "qr_code_json_data"}'
   ```

## 🛠️ Troubleshooting

### Common Issues

1. **Migration Errors**:
   ```bash
   # Reset migrations (development only)
   php artisan migrate:reset
   php artisan migrate --seed
   ```

2. **Permission Issues**:
   ```bash
   # Fix storage permissions
   chmod -R 775 storage
   chmod -R 775 bootstrap/cache
   ```

3. **Queue Jobs Not Processing**:
   ```bash
   # Check queue status
   php artisan queue:failed
   
   # Restart queue worker
   php artisan queue:restart
   ```

4. **Cache Issues**:
   ```bash
   # Clear all caches
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

### Log Files

Monitor these log files for issues:
- `storage/logs/laravel.log` - Application logs
- `storage/logs/visitor-management.log` - Visitor system specific logs

## 📊 Monitoring & Maintenance

### Health Checks

```bash
# Check system health
php artisan visitor:health-check

# Monitor queue status
php artisan queue:monitor

# Check device connectivity
php artisan devices:health-check
```

### Regular Maintenance

```bash
# Clean up expired QR codes (run daily)
php artisan visitor:cleanup-expired

# Archive old visit logs (run monthly)
php artisan visitor:archive-logs

# Clean inactive device tokens (run weekly)
php artisan notifications:cleanup-tokens
```

## 🔒 Security Considerations

1. **API Authentication**: Always use proper API keys in production
2. **HTTPS**: Enable HTTPS for all production deployments
3. **File Permissions**: Restrict file permissions appropriately
4. **Database Security**: Use strong database passwords and restrict access
5. **Regular Updates**: Keep dependencies updated for security patches

## 📞 Support

For technical support:
1. Check the application logs first
2. Review this documentation
3. Test with the provided API endpoints
4. Check database connectivity and migrations

The Enhanced Visitor Management System is now ready to use! Start with the development setup and gradually move to production configuration as needed.