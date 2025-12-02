# Security Service SaaS Platform - Documentation

## Table of Contents

1. [System Overview](#system-overview)
2. [Architecture](#architecture)
3. [API Documentation](#api-documentation)
4. [User Guides](#user-guides)
5. [Deployment Guide](#deployment-guide)
6. [Development Setup](#development-setup)
7. [Testing Strategy](#testing-strategy)
8. [Security & Compliance](#security--compliance)
9. [Troubleshooting](#troubleshooting)
10. [Maintenance & Support](#maintenance--support)

---

## System Overview

The Security Service SaaS Platform is a comprehensive multi-tenant application designed for security agencies to manage their operations, including:

- **Client Management**: Corporate clients requiring security services
- **Employee Management**: Security personnel with comprehensive profiles
- **Attendance Tracking**: Real-time check-in/out with geolocation and QR codes
- **Payroll Processing**: Indian tax regimes, EPF, professional tax calculations
- **Finance & Compliance**: Invoice generation, GST/TDS/PF/ESIC reporting
- **Multi-tenancy**: Complete data isolation between security agencies

### Key Features

- **Multi-tenant Architecture**: Each security agency gets isolated data
- **Dynamic Employee Profiles**: Identity proofs, family details, acquaintances, uniform allocation
- **Advanced Payroll**: Old/New tax regimes, state-specific professional tax
- **Statutory Compliance**: Automated GST, TDS, PF, ESIC report generation
- **Modern Frontend**: React + Inertia.js SPA experience
- **Role-based Security**: 7 user roles with granular permissions
- **Production-ready**: Complete deployment and monitoring setup

---

## Architecture

### Technology Stack

- **Backend**: Laravel 12
- **Frontend**: React 18.2.0 with Inertia.js
- **Database**: MySQL with tenant isolation
- **Multi-tenancy**: `stancl/tenancy` package
- **Admin Interface**: Backpack CRUD v6
- **Authentication**: Laravel Breeze with React scaffolding
- **PDF Generation**: DomPDF for payslips
- **File Storage**: Laravel filesystem with tenant separation
- **Queue Processing**: Redis for background jobs
- **Testing**: PHPUnit + Cypress E2E

### Multi-Tenancy Pattern

```
Central Database (main):
├── tenants (tenant metadata)
├── tenant_subscriptions
├── razorpay_payments
└── system_users

Tenant Databases (tenant_xxx):
├── agencies
├── clients
├── employees
├── employee_identity_proofs
├── employee_family_members
├── employee_acquaintances
├── employee_uniform_allocations
├── attendance_logs
├── payslips
├── invoices
├── invoice_line_items
└── statutory_reports
```

### Directory Structure

```
app/
├── Models/
│   ├── Tenant.php (UUID primary key)
│   ├── Employee.php
│   ├── EmployeeIdentityProof.php
│   ├── EmployeeFamilyMember.php
│   └── ... (tenant-scoped models)
├── Http/
│   ├── Controllers/
│   │   ├── Admin/ (Backpack CRUD controllers)
│   │   ├── Api/ (REST API endpoints)
│   │   └── Auth/ (authentication)
│   └── Middleware/
│       ├── VerifyVisitorApiKey.php
│       └── tenant-aware middleware
├── Services/
│   ├── PayrollCalculator.php
│   ├── RazorpayResolver.php
│   └── FinanceService.php
└── Jobs/ (queue-based background processing)

database/
├── migrations/ (central DB)
└── migrations/tenant/ (tenant-scoped schema)

routes/
├── web.php (central routes)
├── tenant.php (tenant-scoped routes)
└── api.php (API endpoints)
```

---

## API Documentation

### Authentication

All API endpoints require authentication via:
1. **Session-based**: For web interface
2. **API Key**: For visitor devices (`X-API-Key` header)
3. **HMAC Signature**: For secure visitor integrations

### Core Endpoints

#### Employee Management

```http
GET /api/employees
POST /api/employees
GET /api/employees/{id}
PUT /api/employees/{id}
DELETE /api/employees/{id}
```

**Employee Creation Example:**
```json
POST /api/employees
Content-Type: application/json
Authorization: Bearer {token}

{
  "name": "John Security Guard",
  "father_name": "Robert Guard",
  "email": "john.guard@example.com",
  "phone": "+91-9876543210",
  "date_of_birth": "1990-05-15",
  "designation": "Security Guard",
  "client_id": 123,
  "monthly_salary": 25000,
  "joining_date": "2025-01-01",
  "current_address": "123 Guard Colony, Mumbai",
  "permanent_address": "456 Village, Pune",
  "nationality": "Indian",
  "education": "High School",
  "identity_proofs": [
    {
      "type": "aadhar_card",
      "number": "123456789012",
      "image": "base64_encoded_image"
    }
  ],
  "family_members": [
    {
      "name": "Jane Guard",
      "relationship": "spouse",
      "age": 28,
      "phone": "+91-9876543211",
      "nominee": true
    }
  ],
  "acquaintances": [
    {
      "name": "Reference Person",
      "relationship": "friend",
      "phone": "+91-9876543212",
      "address": "789 Reference Street, Mumbai"
    }
  ],
  "uniform_allocations": [
    {
      "client_id": 123,
      "type": "Security Uniform Set",
      "size": "L",
      "quantity": 2,
      "issue_date": "2025-01-01"
    }
  ]
}
```

#### Attendance Management

```http
POST /api/attendance/checkin
POST /api/attendance/checkout
GET /api/attendance/reports
```

**Check-in Example:**
```json
POST /api/attendance/checkin
Content-Type: application/json

{
  "employee_id": 123,
  "check_in_type": "manual", // manual, qr_code, geofence
  "latitude": 19.0760,
  "longitude": 72.8777,
  "qr_code": "optional_qr_code_data"
}
```

#### Payroll Processing

```http
POST /api/payroll/generate
GET /api/payslips
GET /api/payslips/{id}/pdf
```

**Payroll Generation:**
```json
POST /api/payroll/generate
Content-Type: application/json

{
  "period_start": "2025-01-01",
  "period_end": "2025-01-31",
  "tax_regime": "old", // old, new
  "employee_ids": [123, 124, 125] // optional, defaults to all
}
```

#### Finance & Invoicing

```http
GET /api/invoices
POST /api/invoices
PUT /api/invoices/{id}
POST /api/invoices/{id}/payments
GET /api/reports/statutory
```

**Invoice Creation:**
```json
POST /api/invoices
Content-Type: application/json

{
  "client_id": 123,
  "issued_date": "2025-01-01",
  "due_date": "2025-01-31",
  "line_items": [
    {
      "description": "Security Guard Services - January",
      "quantity": 31,
      "unit_price": 1000,
      "tax_rate": 18
    }
  ]
}
```

#### Statutory Reports

```http
GET /api/reports/gst?from=2025-01-01&to=2025-01-31
GET /api/reports/tds?from=2025-01-01&to=2025-01-31
GET /api/reports/pf?from=2025-01-01&to=2025-01-31
GET /api/reports/esic?from=2025-01-01&to=2025-01-31
```

### Error Handling

All API endpoints return consistent error responses:

```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid.",
    "details": {
      "name": ["The name field is required."],
      "email": ["The email field is required."]
    }
  }
}
```

Common HTTP Status Codes:
- `200`: Success
- `201`: Created
- `400`: Bad Request
- `401`: Unauthorized
- `403`: Forbidden
- `404`: Not Found
- `422`: Validation Error
- `500`: Internal Server Error

---

## User Guides

### For Security Agency Owners

#### Initial Setup

1. **Agency Registration**
   - Register your security agency
   - Set up company details and branding
   - Configure operational parameters

2. **User Management**
   - Create HR staff accounts
   - Assign appropriate roles and permissions
   - Set up client access if needed

3. **Client Onboarding**
   - Add corporate clients
   - Define service agreements
   - Set up billing parameters

#### Employee Management

1. **Adding New Employees**
   - Navigate to Admin → Employees → Add New
   - Fill in basic information (name, contact details)
   - Add employment details (designation, salary, client assignment)
   - Upload identity proofs (Aadhar, PAN, etc.)
   - Add family member details for EPF/ESIC
   - Record acquaintances for background verification
   - Allocate uniforms and equipment

2. **Managing Employee Profiles**
   - Edit employee information as needed
   - Add/remove identity documents
   - Update family member information
   - Track uniform allocations and returns

3. **Employee Lifecycle**
   - Onboarding new hires
   - Regular profile updates
   - Exit procedures and documentation

### For HR Staff

#### Daily Operations

1. **Attendance Management**
   - Monitor real-time check-ins/check-outs
   - Handle manual attendance corrections
   - Generate attendance reports

2. **Payroll Processing**
   - Monthly payroll generation
   - Tax regime selection (old/new)
   - Payslip distribution
   - Statutory compliance reporting

3. **Compliance Management**
   - EPF contribution tracking
   - ESIC premium calculations
   - Professional tax by state
   - TDS management

### For Clients

#### Self-Service Features

1. **Employee Monitoring**
   - View assigned security personnel
   - Real-time attendance tracking
   - Performance metrics

2. **Service Reports**
   - Monthly service summaries
   - Incident reports
   - Billing transparency

### For Security Guards/Employees

#### Mobile Interface

1. **Attendance Tracking**
   - Check-in/check-out via mobile
   - QR code scanning at client premises
   - Geolocation verification

2. **Profile Management**
   - View personal information
   - Access payslips
   - Update contact details

---

## Deployment Guide

### Production Environment Requirements

- **Server**: Ubuntu 20.04+ or CentOS 8+
- **PHP**: 8.2+
- **MySQL**: 8.0+
- **Redis**: 6.0+
- **Node.js**: 18+
- **Nginx**: 1.18+
- **Supervisor**: 4.2+

### Deployment Steps

1. **Server Preparation**
   ```bash
   # Use provided deployment script
   chmod +x deploy.sh
   ./deploy.sh
   ```

2. **Environment Configuration**
   ```bash
   # Copy production environment file
   cp .env.production .env
   
   # Generate application key
   php artisan key:generate
   
   # Configure database and other services
   ```

3. **Database Setup**
   ```bash
   # Run central migrations
   php artisan migrate --force
   
   # Seed initial data
   php artisan db:seed --force
   ```

4. **Asset Compilation**
   ```bash
   # Install and build frontend assets
   npm install
   npm run build
   ```

5. **Queue Processing Setup**
   ```bash
   # Copy supervisor configuration
   sudo cp config/supervisor/laravel-worker.conf /etc/supervisor/conf.d/
   sudo supervisorctl reread
   sudo supervisorctl update
   sudo supervisorctl start laravel-worker:*
   ```

6. **Web Server Configuration**
   ```bash
   # Copy nginx configuration
   sudo cp config/nginx/app.conf /etc/nginx/sites-available/security-saas
   sudo ln -s /etc/nginx/sites-available/security-saas /etc/nginx/sites-enabled/
   sudo nginx -t
   sudo systemctl reload nginx
   ```

7. **SSL Certificate**
   ```bash
   # Install Let's Encrypt certificate
   sudo certbot --nginx -d yourdomain.com
   ```

### Monitoring Setup

1. **Log Monitoring**
   ```bash
   # Application logs
   tail -f storage/logs/laravel.log
   
   # Queue processing logs
   sudo supervisorctl tail laravel-worker
   ```

2. **Performance Monitoring**
   - Set up application performance monitoring
   - Configure database query monitoring
   - Monitor queue processing

### Backup Strategy

1. **Database Backups**
   ```bash
   # Automated daily backups
   0 2 * * * /path/to/backup-script.sh
   ```

2. **File Backups**
   - User uploaded files
   - Configuration files
   - SSL certificates

---

## Development Setup

### Local Development Environment

1. **Prerequisites**
   - PHP 8.2+
   - Composer
   - Node.js 18+
   - MySQL 8.0+
   - Redis

2. **Installation**
   ```bash
   # Clone repository
   git clone <repository-url>
   cd laravelbackpack
   
   # Install dependencies
   composer install
   npm install
   
   # Environment setup
   cp .env.example .env
   php artisan key:generate
   
   # Database setup
   php artisan migrate --seed
   
   # Frontend build
   npm run dev
   ```

3. **Development Services**
   ```bash
   # Run development server with all services
   composer run-script dev
   ```

### Testing Setup

1. **Unit Tests**
   ```bash
   composer test
   ```

2. **E2E Tests**
   ```bash
   npx cypress open
   ```

### Code Quality Tools

1. **Static Analysis**
   ```bash
   ./vendor/bin/phpstan analyse
   ```

2. **Code Style**
   ```bash
   ./vendor/bin/php-cs-fixer fix
   ```

---

## Testing Strategy

### Test Coverage

1. **Unit Tests** (85% coverage)
   - Model relationships and business logic
   - Service class functionality
   - Utility functions

2. **Feature Tests** (95% coverage)
   - API endpoint testing
   - Authentication flows
   - Multi-tenancy isolation
   - Role-based access control

3. **Integration Tests**
   - PayrollCalculator with tax regimes
   - Attendance and payroll workflows
   - Finance and compliance reporting
   - Complete employee lifecycle

4. **E2E Tests** (Cypress)
   - Complete user workflows
   - Multi-tenant data isolation
   - File upload handling
   - Error scenarios

### Test Data Management

1. **Factories and Seeders**
   - Comprehensive test data generation
   - Tenant-aware data seeding
   - Realistic test scenarios

2. **Database Testing**
   - Transaction-based test isolation
   - Multi-tenancy testing patterns
   - Data integrity validation

---

## Security & Compliance

### Security Features

1. **Multi-tenancy Security**
   - Complete data isolation between tenants
   - Tenant-aware middleware
   - Cross-tenant access prevention

2. **Authentication & Authorization**
   - Role-based access control (7 roles)
   - API key authentication for devices
   - HMAC signature validation
   - Session management

3. **Data Protection**
   - File upload validation
   - SQL injection prevention
   - XSS protection
   - CSRF tokens

4. **Audit Logging**
   - User action tracking
   - Data modification logs
   - System access logs

### Compliance Features

1. **Indian Statutory Compliance**
   - EPF calculations and reporting
   - ESIC premium calculations
   - Professional tax by state
   - TDS computations

2. **GST Compliance**
   - Automated GST calculations
   - GSTR export formats
   - Tax rate management

3. **Data Privacy**
   - Employee data protection
   - Client information security
   - GDPR-ready features

---

## Troubleshooting

### Common Issues

1. **Tenant Database Issues**
   ```bash
   # Check tenant database creation
   php artisan tenants:list
   
   # Recreate tenant database
   php artisan tenants:migrate-fresh --tenants=<tenant-id>
   ```

2. **Queue Processing Issues**
   ```bash
   # Check queue status
   php artisan queue:failed
   
   # Restart queue workers
   sudo supervisorctl restart laravel-worker:*
   ```

3. **File Upload Issues**
   ```bash
   # Check storage permissions
   chmod -R 755 storage/
   chown -R www-data:www-data storage/
   
   # Clear file cache
   php artisan storage:link
   ```

4. **Performance Issues**
   ```bash
   # Clear application cache
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   
   # Optimize for production
   php artisan optimize
   ```

### Debug Mode

```php
// .env configuration for debugging
APP_DEBUG=true
LOG_LEVEL=debug

// Enable query logging
DB_LOG_QUERIES=true
```

### Error Reporting

1. **Application Logs**
   - Location: `storage/logs/laravel.log`
   - Daily rotation enabled
   - Structured error reporting

2. **Queue Logs**
   - Failed job tracking
   - Retry mechanisms
   - Error notification system

---

## Maintenance & Support

### Regular Maintenance Tasks

1. **Daily**
   - Monitor queue processing
   - Check application logs
   - Verify backup completion

2. **Weekly**
   - Database performance review
   - Security scan execution
   - Storage cleanup

3. **Monthly**
   - Dependency updates
   - Performance optimization
   - Capacity planning review

### Update Procedures

1. **Code Updates**
   ```bash
   # Backup current version
   cp -r /var/www/html/security-saas /var/www/backup/
   
   # Deploy new version
   git pull origin main
   composer install --no-dev
   npm run build
   php artisan migrate --force
   php artisan optimize
   ```

2. **Security Updates**
   - Immediate deployment for security patches
   - Dependency vulnerability scanning
   - Regular security audits

### Support Contacts

- **Technical Support**: tech-support@yourcompany.com
- **Emergency Contact**: +91-XXXX-XXXXXX
- **Documentation Updates**: docs@yourcompany.com

### Service Level Agreements

- **Uptime**: 99.9%
- **Response Time**: < 4 hours for critical issues
- **Data Recovery**: 24-hour maximum recovery time
- **Support Hours**: 24/7 for critical issues, business hours for general support

---

## Appendices

### A. Configuration Reference

#### Environment Variables
```bash
# Core Application
APP_NAME="Security Service SaaS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=security_saas
DB_USERNAME=security_user
DB_PASSWORD=secure_password

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Queue
QUEUE_CONNECTION=redis

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.yourprovider.com
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password

# Razorpay
RAZORPAY_KEY=rzp_live_xxxxx
RAZORPAY_SECRET=xxxxx

# File Storage
FILESYSTEM_DISK=local

# Visitor API
VISITOR_API_KEY=your_secure_api_key
VISITOR_HMAC_SECRET=your_hmac_secret
```

### B. API Rate Limits

| Endpoint | Rate Limit | Window |
|----------|------------|---------|
| Authentication | 5 requests | 1 minute |
| Employee APIs | 100 requests | 1 minute |
| Attendance APIs | 200 requests | 1 minute |
| Finance APIs | 50 requests | 1 minute |
| Reports | 10 requests | 1 minute |

### C. Database Schema Reference

See `database/migrations/` and `database/migrations/tenant/` for complete schema definitions.

---

*This documentation is maintained by the development team. Last updated: January 2025*