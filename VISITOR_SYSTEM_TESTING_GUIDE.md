# 🚀 Enhanced Visitor Management System - Quick Start Guide

## Prerequisites Check

Before starting, ensure you have:
```bash
# Check PHP version (needs 8.1+)
php -v

# Check Composer
composer --version

# Check Node.js (for frontend assets)
node -v
npm -v
```

## Step 1: Environment Setup

### 1.1 Install Dependencies
```bash
# Navigate to project directory
cd /Users/admin/Desktop/laravelbackpack

# Install PHP dependencies
composer install

# Install JavaScript dependencies  
npm install
```

### 1.2 Environment Configuration
```bash
# Copy environment file (if not exists)
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 1.3 Configure .env File
Edit your `.env` file with these settings:
```env
APP_NAME="Enhanced Visitor Management"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database (SQLite for quick start)
DB_CONNECTION=sqlite
DB_DATABASE=/Users/admin/Desktop/laravelbackpack/database/database.sqlite

# Queue (for background jobs)
QUEUE_CONNECTION=database

# Session and Cache
SESSION_DRIVER=database
CACHE_STORE=database

# Optional: Firebase for push notifications
FIREBASE_SERVER_KEY=your_firebase_key_here

# Optional: Visitor API keys
VISITOR_API_KEY=test-api-key-123
VISITOR_HMAC_SECRET=test-hmac-secret
```

## Step 2: Database Setup

### 2.1 Create SQLite Database
```bash
# Create database file
touch database/database.sqlite
```

### 2.2 Run Migrations
```bash
# Run central database migrations
php artisan migrate

# Seed with default data
php artisan db:seed

# Create a test tenant
php artisan tenants:create test-company.local

# Run tenant migrations
php artisan tenants:migrate
```

## Step 3: Storage Setup

```bash
# Create storage link for file uploads
php artisan storage:link

# Create visitor directories
mkdir -p storage/app/public/visitors/photos
mkdir -p storage/app/public/visitors/documents
```

## Step 4: Start the System

### 4.1 Start Laravel Server
```bash
# Start development server
php artisan serve
# Server will run on http://127.0.0.1:8000
```

### 4.2 Start Queue Worker (Optional - for background jobs)
```bash
# In a new terminal tab
php artisan queue:work
```

### 4.3 Compile Assets (Optional - if using frontend)
```bash
# In another terminal tab
npm run dev
```

## Step 5: Test the System

### 5.1 Test Basic Connectivity
```bash
# Test server is running
curl http://127.0.0.1:8000

# Test API health
curl -H "Accept: application/json" http://127.0.0.1:8000/api/mobile/config
```

### 5.2 Test Mobile App Features
```bash
# Test mobile configuration endpoint
curl -s http://127.0.0.1:8000/api/mobile/config | jq

# Test visitor invitations (should return empty array)
curl -s "http://127.0.0.1:8000/api/mobile/invitations?phone=1234567890" | jq

# Test visitor status endpoint
curl -s "http://127.0.0.1:8000/api/mobile/visitor-status?visitor_code=TEST123" | jq
```

### 5.3 Create Test Data
```bash
# Login to get authentication token (create user first if needed)
TOKEN=$(curl -s -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@test.com","password":"password"}' | jq -r '.token')

# Create a test visitor
curl -X POST http://127.0.0.1:8000/api/visitors \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "phone": "+1234567890", 
    "email": "john@example.com",
    "company": "Test Company",
    "purpose_of_visit": "meeting"
  }'
```

## Step 6: Access the System

### 6.1 Web Interface
- **Main App**: http://127.0.0.1:8000
- **Admin Panel**: http://127.0.0.1:8000/admin (if configured)

### 6.2 API Endpoints

#### Public Endpoints (No authentication required)
```bash
# Mobile app configuration
GET http://127.0.0.1:8000/api/mobile/config

# Generate visitor QR code
POST http://127.0.0.1:8000/api/mobile/generate-qr
Body: {"visitor_code": "VIS123"}

# Check visitor invitations
GET http://127.0.0.1:8000/api/mobile/invitations?phone=1234567890

# Get visitor status  
GET http://127.0.0.1:8000/api/mobile/visitor-status?visitor_code=VIS123
```

#### Authenticated Endpoints (Require Bearer token)
```bash
# Visitor CRUD operations
GET    http://127.0.0.1:8000/api/visitors
POST   http://127.0.0.1:8000/api/visitors  
GET    http://127.0.0.1:8000/api/visitors/{id}
PUT    http://127.0.0.1:8000/api/visitors/{id}
DELETE http://127.0.0.1:8000/api/visitors/{id}

# Analytics and reporting
GET    http://127.0.0.1:8000/api/visitor-analytics/dashboard
GET    http://127.0.0.1:8000/api/visitor-analytics/compliance-report

# IoT device management
GET    http://127.0.0.1:8000/api/devices
POST   http://127.0.0.1:8000/api/devices
```

## Step 7: Testing Scenarios

### 7.1 Complete Visitor Workflow Test
```bash
# 1. Create visitor invitation
curl -X POST http://127.0.0.1:8000/api/visitor-invitations \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "visitor_name": "Jane Smith",
    "visitor_phone": "+0987654321",
    "visitor_email": "jane@company.com", 
    "purpose_of_visit": "interview",
    "scheduled_date": "2025-11-10"
  }'

# 2. Generate QR code for visitor
curl -X POST http://127.0.0.1:8000/api/mobile/generate-qr \
  -H "Content-Type: application/json" \
  -d '{"visitor_code": "VIS123"}'

# 3. Simulate QR scan check-in
curl -X POST http://127.0.0.1:8000/api/mobile/scan-qr \
  -H "Content-Type: application/json" \
  -d '{"qr_data": "{\"visitor_id\":1,\"visitor_code\":\"VIS123\"}"}'
```

### 7.2 Test Real-time Dashboard
```bash
# Get dashboard data
curl -H "Authorization: Bearer $TOKEN" \
  http://127.0.0.1:8000/api/visitor-analytics/dashboard | jq
```

### 7.3 Test IoT Device Integration
```bash
# Register IoT device
curl -X POST http://127.0.0.1:8000/api/devices \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "device_type": "tablet",
    "device_name": "Reception Kiosk 1",
    "location": "Main Entrance"
  }'

# Send device heartbeat
curl -X POST http://127.0.0.1:8000/api/iot/heartbeat \
  -H "X-API-Key: test-api-key-123" \
  -H "Content-Type: application/json" \
  -d '{"device_id": "DEVICE123", "status": "online"}'
```

## Step 8: Frontend Testing (Optional)

If you want to test with a frontend interface:

### 8.1 Using Postman/Insomnia
- Import the API endpoints into Postman
- Set up environment variables for base URL and auth token
- Test all endpoints systematically

### 8.2 Using Browser Developer Tools
```javascript
// Test in browser console (after getting auth token)
const token = 'your-auth-token-here';

// Test mobile config
fetch('http://127.0.0.1:8000/api/mobile/config')
  .then(r => r.json())
  .then(console.log);

// Test authenticated endpoint
fetch('http://127.0.0.1:8000/api/visitors', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
}).then(r => r.json()).then(console.log);
```

## Step 9: Advanced Features Testing

### 9.1 Photo Upload Test
```bash
# Test photo upload (create a test image first)
curl -X POST http://127.0.0.1:8000/api/mobile/upload-photo \
  -F "visitor_code=VIS123" \
  -F "photo=@test-photo.jpg"
```

### 9.2 Push Notification Test
```bash
# Test push notification
curl -X POST http://127.0.0.1:8000/api/mobile/test-notification \
  -H "Content-Type: application/json" \
  -d '{
    "push_token": "test-firebase-token",
    "title": "Test Notification", 
    "message": "This is a test message"
  }'
```

### 9.3 Analytics Export Test
```bash
# Test data export
curl -X POST http://127.0.0.1:8000/api/visitor-analytics/export-visitor-data \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "start_date": "2025-11-01",
    "end_date": "2025-11-10",
    "format": "json"
  }'
```

## Troubleshooting

### Common Issues and Solutions

1. **Server won't start**:
   ```bash
   # Check if port 8000 is in use
   lsof -i :8000
   # Kill process if needed, then restart
   ```

2. **Database errors**:
   ```bash
   # Reset migrations (development only)
   php artisan migrate:reset
   php artisan migrate --seed
   ```

3. **API returns 404**:
   ```bash
   # Clear route cache
   php artisan route:clear
   php artisan route:cache
   ```

4. **Authentication issues**:
   ```bash
   # Clear all caches
   php artisan cache:clear
   php artisan config:clear
   ```

5. **Storage permission issues**:
   ```bash
   # Fix permissions
   chmod -R 775 storage bootstrap/cache
   ```

## System Features Available

✅ **Visitor Registration**: Create, update, and manage visitor profiles
✅ **QR Code Generation**: Dynamic QR codes for contactless check-in
✅ **Mobile Integration**: Mobile app endpoints for visitors and staff  
✅ **IoT Device Support**: Integration with kiosks, tablets, and sensors
✅ **Real-time Dashboard**: Live visitor tracking and analytics
✅ **Security Features**: Background checks, watchlists, and approvals
✅ **Compliance Reporting**: Export data for audits and compliance
✅ **Push Notifications**: Real-time alerts and updates
✅ **Photo Management**: Visitor photo capture and storage
✅ **Multi-tenant Architecture**: Separate data for each client/tenant

The system is now ready for full testing and development! 🎉