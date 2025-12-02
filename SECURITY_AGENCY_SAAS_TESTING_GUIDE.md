# 🛡️ Security Guard Agency SaaS - Complete Platform Testing Guide

## 🏢 Platform Overview

This is a comprehensive **multi-tenant SaaS platform for Security Guard Agencies** to manage their entire business operations. Visitor management is just one security service module among many core business features.

### 🎯 Core Business Modules:
1. **👥 Employee/Guard Management** - Core workforce management
2. **💰 Payroll & Salary Processing** - Indian tax compliance (EPF, ESIC, GST)
3. **⏰ Attendance Management** - Guard check-in/out, shift tracking
4. **🏢 Client Management** - Security contracts and site assignments
5. **📋 Agency Operations** - Multi-agency management for platform
6. **💼 Financial Management** - Invoicing, payments, compliance
7. **👤 Visitor Management** - One security service offered to clients
8. **📊 Analytics & Reporting** - Business intelligence and compliance

---

## 🚀 Quick Start Guide

### Prerequisites Check
```bash
# Check system requirements
php -v              # PHP 8.1+
composer --version  # Composer
node -v            # Node.js 18+
npm -v             # NPM
```

### Step 1: Environment Setup

```bash
# Navigate to project directory
cd /Users/admin/Desktop/laravelbackpack

# Install dependencies
composer install
npm install

# Environment configuration
cp .env.example .env
php artisan key:generate
```

### Step 2: Database Setup

```bash
# Create SQLite database (for quick testing)
touch database/database.sqlite

# Configure .env file
```

Edit `.env` with these settings:
```env
APP_NAME="Security Agency SaaS"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=sqlite
DB_DATABASE=/Users/admin/Desktop/laravelbackpack/database/database.sqlite

# Queue & Cache
QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_STORE=database

# API Keys
VISITOR_API_KEY=test-api-key-123
VISITOR_HMAC_SECRET=test-hmac-secret
```

```bash
# Run migrations and seed data
php artisan migrate
php artisan db:seed

# Create test tenant (security agency)
php artisan tenants:create test-agency.local
php artisan tenants:migrate

# Create storage links
php artisan storage:link
mkdir -p storage/app/public/{employees,visitors,documents}
```

### Step 3: Start the Platform

```bash
# Terminal 1: Start Laravel server
php artisan serve

# Terminal 2: Start queue worker (for background jobs)
php artisan queue:work

# Terminal 3: Compile frontend assets (optional)
npm run dev
```

---

## 🧪 Complete Platform Testing

### 1. 👥 **Employee/Guard Management Testing**

#### Get Authentication Token
```bash
TOKEN=$(curl -s -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@test.com","password":"password"}' | jq -r '.token')
```

#### Test Employee CRUD Operations
```bash
# Create new security guard
curl -X POST http://127.0.0.1:8000/api/employees \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "Rajesh",
    "last_name": "Kumar", 
    "email": "rajesh.guard@agency.com",
    "phone": "+919876543210",
    "monthly_salary": 25000,
    "job_role": "Security Guard",
    "shift": ["night", "weekend"],
    "state": "maharashtra"
  }'

# Get all employees
curl -H "Authorization: Bearer $TOKEN" \
  http://127.0.0.1:8000/api/employees | jq

# Upload KYC documents (requires multipart)
curl -X POST http://127.0.0.1:8000/api/employees/1 \
  -H "Authorization: Bearer $TOKEN" \
  -F "aadhar=@/path/to/aadhar.pdf" \
  -F "pan=@/path/to/pan.pdf" \
  -F "police_verification=@/path/to/verification.pdf"
```

### 2. 🏢 **Client Management Testing**

```bash
# Create client (company that hires security services)
curl -X POST http://127.0.0.1:8000/api/clients \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Tech Corp Pvt Ltd",
    "email": "security@techcorp.com",
    "contact_person": "HR Manager",
    "phone": "+911234567890",
    "address": "Whitefield, Bangalore",
    "contract_start": "2025-01-01",
    "contract_end": "2025-12-31",
    "monthly_fee": 150000
  }'

# Get all clients
curl -H "Authorization: Bearer $TOKEN" \
  http://127.0.0.1:8000/api/clients | jq

# Assign guards to client site
curl -X POST http://127.0.0.1:8000/api/clients/1/assign-guards \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "employee_ids": [1, 2],
    "site_location": "Main Gate",
    "shift_timings": "24x7 rotation"
  }'
```

### 3. ⏰ **Attendance Management Testing**

```bash
# Guard check-in
curl -X POST http://127.0.0.1:8000/api/attendance/checkin \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "employee_id": 1,
    "client_site_id": 1,
    "location": "Main Gate",
    "check_in_method": "mobile_app"
  }'

# Guard check-out
curl -X POST http://127.0.0.1:8000/api/attendance/checkout \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "employee_id": 1,
    "notes": "Shift completed successfully"
  }'

# Get attendance reports
curl -H "Authorization: Bearer $TOKEN" \
  "http://127.0.0.1:8000/api/attendance/reports?start_date=2025-11-01&end_date=2025-11-30" | jq

# Export attendance data
curl -X POST http://127.0.0.1:8000/api/attendance/export \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "start_date": "2025-11-01",
    "end_date": "2025-11-30",
    "format": "csv"
  }'
```

### 4. 💰 **Payroll & Salary Processing Testing**

```bash
# Run payroll for employees
curl -X POST http://127.0.0.1:8000/api/payslips/run \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "period_start": "2025-11-01",
    "period_end": "2025-11-30",
    "regime": "old"
  }'

# Get payslip details
curl -H "Authorization: Bearer $TOKEN" \
  http://127.0.0.1:8000/api/payslips/employee/1 | jq

# Download payslip PDF
curl -H "Authorization: Bearer $TOKEN" \
  http://127.0.0.1:8000/api/payslips/1/pdf \
  -o employee_payslip.pdf

# Test Indian tax calculations
curl -X POST http://127.0.0.1:8000/api/payroll/calculate \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "base_salary": 25000,
    "allowances": 5000,
    "deductions": 1000,
    "state": "maharashtra",
    "regime": "new"
  }'
```

### 5. 💼 **Financial Management Testing**

```bash
# Create invoice for client
curl -X POST http://127.0.0.1:8000/api/finance/invoices \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": 1,
    "issued_date": "2025-11-01",
    "due_date": "2025-11-30",
    "items": [
      {
        "description": "Security Services - November 2025",
        "qty": 1,
        "unit_price": 150000,
        "tax_rate": 18
      }
    ]
  }'

# Record payment
curl -X POST http://127.0.0.1:8000/api/finance/invoices/1/payments \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 177000,
    "method": "bank_transfer",
    "reference": "TXN123456"
  }'

# Generate GST report
curl -X POST http://127.0.0.1:8000/api/finance/reports/statutory \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "gst",
    "period_start": "2025-11-01",
    "period_end": "2025-11-30"
  }'

# Get profitability dashboard
curl -H "Authorization: Bearer $TOKEN" \
  http://127.0.0.1:8000/api/finance/profitability | jq
```

### 6. 📋 **Agency Operations Testing**

```bash
# Create new agency (Super Admin only)
curl -X POST http://127.0.0.1:8000/api/agencies \
  -H "Authorization: Bearer $SUPER_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Elite Security Services",
    "license_number": "SEC2025001",
    "contact_person": "Agency Owner",
    "email": "owner@elitesecurity.com",
    "phone": "+919123456789",
    "address": "Mumbai, Maharashtra"
  }'

# Get agency dashboard
curl -H "Authorization: Bearer $TOKEN" \
  http://127.0.0.1:8000/api/agencies/dashboard | jq

# Agency performance metrics
curl -H "Authorization: Bearer $TOKEN" \
  http://127.0.0.1:8000/api/agencies/1/metrics | jq
```

### 7. 👤 **Visitor Management Testing** (Security Service Module)

```bash
# Visitor check-in (at client site)
curl -X POST http://127.0.0.1:8000/api/visitors/checkin \
  -H "X-API-Key: test-api-key-123" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Visitor",
    "phone": "+919876543210",
    "email": "john@company.com",
    "company": "Vendor Corp",
    "purpose": "Business meeting",
    "host_contact": "+919123456789",
    "client_site_id": 1
  }'

# Get visitor dashboard for client site
curl -H "Authorization: Bearer $TOKEN" \
  http://127.0.0.1:8000/api/visitor-analytics/dashboard | jq

# Visitor check-out
curl -X POST http://127.0.0.1:8000/api/visitors/1/checkout \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "checkout_reason": "Meeting completed",
    "rating": 5
  }'
```

### 8. 📊 **Analytics & Reporting Testing**

```bash
# Business dashboard
curl -H "Authorization: Bearer $TOKEN" \
  http://127.0.0.1:8000/api/dashboard | jq

# Employee performance report
curl -H "Authorization: Bearer $TOKEN" \
  "http://127.0.0.1:8000/api/reports/employee-performance?period=monthly" | jq

# Client satisfaction metrics
curl -H "Authorization: Bearer $TOKEN" \
  http://127.0.0.1:8000/api/reports/client-satisfaction | jq

# Financial summary
curl -H "Authorization: Bearer $TOKEN" \
  "http://127.0.0.1:8000/api/reports/financial-summary?year=2025" | jq
```

---

## 🌐 Frontend Interface Testing

Since you mentioned wanting to test with a frontend interface, here are the available options:

### Option 1: Admin Panel (Backpack)
```bash
# Access admin panel
open http://127.0.0.1:8000/admin

# Login with super admin credentials
# Email: admin@test.com
# Password: password
```

**Available Admin Modules:**
- Agencies Management
- Employees Management  
- Clients Management
- Attendance Records
- Payroll Management
- Invoice Management
- Users & Roles
- Tenancy Management

### Option 2: React/Inertia Frontend
```bash
# Compile frontend assets
npm run dev

# Access SPA frontend
open http://127.0.0.1:8000

# Role-based dashboards available for:
# - Super Admin
# - Agency Owner  
# - HR Staff
# - Client
# - Security Guards
```

### Option 3: API Testing Tools

**Using Postman:**
1. Import API collection (create from endpoints above)
2. Set environment variables:
   - `base_url`: http://127.0.0.1:8000
   - `auth_token`: Get from login endpoint

**Using Insomnia:**
1. Create workspace for "Security Agency SaaS"
2. Configure auth token globally
3. Test all module endpoints

---

## 🧪 End-to-End Business Flow Testing

### Complete Agency Workflow:

```bash
# 1. Agency signs up and onboards employees
# 2. Client contracts agency for security services  
# 3. Guards assigned to client sites
# 4. Daily attendance tracking
# 5. Monthly payroll processing
# 6. Client invoicing
# 7. Visitor management at client sites
# 8. Analytics and compliance reporting
```

### Test Scenarios:

1. **New Agency Onboarding**
2. **Employee Recruitment & KYC**
3. **Client Contract Management**
4. **Guard Assignment & Scheduling**
5. **Daily Operations Management**
6. **Monthly Payroll Run**
7. **Client Billing & Collections**
8. **Regulatory Compliance Reports**

---

## 🚨 Troubleshooting

### Common Issues:

1. **Database Connection**:
   ```bash
   php artisan migrate:reset
   php artisan migrate --seed
   ```

2. **Permission Issues**:
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

3. **Cache Issues**:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   ```

### Support:
- Check logs: `storage/logs/laravel.log`
- Queue status: Monitor queue jobs
- API responses: Use proper error handling

---

## 🎯 Platform Features Summary

### ✅ **Core Business Management:**
- Multi-tenant agency management
- Employee lifecycle management
- Client contract management  
- Attendance & shift tracking
- Indian payroll compliance
- Financial management & invoicing

### ✅ **Security Services:**
- Visitor management system
- Guard deployment tracking
- Site security monitoring
- Incident reporting

### ✅ **Business Intelligence:**
- Real-time dashboards
- Performance analytics
- Financial reporting
- Compliance tracking

### ✅ **Technical Features:**
- Multi-tenant SaaS architecture
- Role-based access control
- API-first design
- Mobile app integration
- Automated workflows

**The platform is ready for comprehensive testing across all business modules!** 🚀