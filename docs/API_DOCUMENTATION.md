# API Documentation - Security Service SaaS

## Base URL
```
Production: https://yourdomain.com/api
Staging: https://staging.yourdomain.com/api
Development: http://localhost:8000/api
```

## Authentication

### API Key Authentication
```http
X-API-Key: your_api_key_here
Content-Type: application/json
```

### HMAC Signature Authentication
```http
X-HMAC-Signature: sha256=calculated_signature
X-HMAC-Timestamp: unix_timestamp
Content-Type: application/json
```

HMAC Calculation:
```javascript
const crypto = require('crypto');
const payload = JSON.stringify(requestBody);
const timestamp = Math.floor(Date.now() / 1000);
const message = `${timestamp}.${payload}`;
const signature = crypto.createHmac('sha256', secret).update(message).digest('hex');
```

## Core Resources

### 1. Employees

#### List Employees
```http
GET /employees?page=1&per_page=20&search=john
```

**Query Parameters:**
- `page` (integer): Page number for pagination
- `per_page` (integer): Items per page (max 100)
- `search` (string): Search in name, email, phone
- `client_id` (integer): Filter by client
- `designation` (string): Filter by designation

**Response:**
```json
{
  "data": [
    {
      "id": 123,
      "name": "John Security Guard",
      "father_name": "Robert Guard",
      "email": "john.guard@example.com",
      "phone": "+91-9876543210",
      "date_of_birth": "1990-05-15",
      "designation": "Security Guard",
      "client": {
        "id": 1,
        "name": "Corporate Client Ltd"
      },
      "monthly_salary": 25000,
      "joining_date": "2025-01-01",
      "current_address": "123 Guard Colony, Mumbai",
      "permanent_address": "456 Village, Pune",
      "nationality": "Indian",
      "education": "High School",
      "created_at": "2025-01-01T00:00:00Z",
      "updated_at": "2025-01-01T00:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 50,
    "last_page": 3
  },
  "links": {
    "first": "/api/employees?page=1",
    "last": "/api/employees?page=3",
    "prev": null,
    "next": "/api/employees?page=2"
  }
}
```

#### Get Single Employee
```http
GET /employees/{id}?include=identity_proofs,family_members,acquaintances,uniform_allocations
```

**Query Parameters:**
- `include` (string): Comma-separated list of relationships to include

**Response:**
```json
{
  "data": {
    "id": 123,
    "name": "John Security Guard",
    "father_name": "Robert Guard",
    "email": "john.guard@example.com",
    "phone": "+91-9876543210",
    "date_of_birth": "1990-05-15",
    "designation": "Security Guard",
    "client_id": 1,
    "monthly_salary": 25000,
    "joining_date": "2025-01-01",
    "current_address": "123 Guard Colony, Mumbai",
    "permanent_address": "456 Village, Pune",
    "nationality": "Indian",
    "education": "High School",
    "bank_name": "State Bank of India",
    "account_number": "1234567890123456",
    "ifsc_code": "SBIN0001234",
    "account_holder_name": "John Security Guard",
    "identity_proofs": [
      {
        "id": 1,
        "type": "aadhar_card",
        "number": "123456789012",
        "image_path": "/storage/identity/aadhar_123.jpg",
        "created_at": "2025-01-01T00:00:00Z"
      },
      {
        "id": 2,
        "type": "pan_card",
        "number": "ABCDE1234F",
        "image_path": "/storage/identity/pan_123.jpg",
        "created_at": "2025-01-01T00:00:00Z"
      }
    ],
    "family_members": [
      {
        "id": 1,
        "name": "Jane Guard",
        "relationship": "spouse",
        "age": 28,
        "phone": "+91-9876543211",
        "nominee": true,
        "created_at": "2025-01-01T00:00:00Z"
      }
    ],
    "acquaintances": [
      {
        "id": 1,
        "name": "Reference Person",
        "relationship": "friend",
        "phone": "+91-9876543212",
        "address": "789 Reference Street, Mumbai",
        "created_at": "2025-01-01T00:00:00Z"
      }
    ],
    "uniform_allocations": [
      {
        "id": 1,
        "client_id": 1,
        "type": "Security Uniform Set",
        "size": "L",
        "quantity": 2,
        "issue_date": "2025-01-01",
        "return_date": null,
        "created_at": "2025-01-01T00:00:00Z"
      }
    ],
    "created_at": "2025-01-01T00:00:00Z",
    "updated_at": "2025-01-01T00:00:00Z"
  }
}
```

#### Create Employee
```http
POST /employees
Content-Type: application/json
```

**Request Body:**
```json
{
  "name": "John Security Guard",
  "father_name": "Robert Guard",
  "email": "john.guard@example.com",
  "phone": "+91-9876543210",
  "date_of_birth": "1990-05-15",
  "designation": "Security Guard",
  "client_id": 1,
  "monthly_salary": 25000,
  "joining_date": "2025-01-01",
  "current_address": "123 Guard Colony, Mumbai",
  "permanent_address": "456 Village, Pune",
  "nationality": "Indian",
  "education": "High School",
  "bank_name": "State Bank of India",
  "account_number": "1234567890123456",
  "ifsc_code": "SBIN0001234",
  "account_holder_name": "John Security Guard",
  "identity_proofs": [
    {
      "type": "aadhar_card",
      "number": "123456789012",
      "image": "base64_encoded_image_data"
    },
    {
      "type": "pan_card",
      "number": "ABCDE1234F",
      "image": "base64_encoded_image_data"
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
      "client_id": 1,
      "type": "Security Uniform Set",
      "size": "L",
      "quantity": 2,
      "issue_date": "2025-01-01"
    }
  ]
}
```

**Response:**
```json
{
  "data": {
    "id": 123,
    "name": "John Security Guard",
    // ... full employee data
  },
  "message": "Employee created successfully"
}
```

#### Update Employee
```http
PUT /employees/{id}
Content-Type: application/json
```

Same request body structure as creation, all fields optional.

#### Delete Employee
```http
DELETE /employees/{id}
```

**Response:**
```json
{
  "message": "Employee deleted successfully"
}
```

### 2. Attendance

#### Check-in Employee
```http
POST /attendance/checkin
Content-Type: application/json
```

**Request Body:**
```json
{
  "employee_id": 123,
  "check_in_type": "manual", // manual, qr_code, geofence
  "latitude": 19.0760,
  "longitude": 72.8777,
  "qr_code": "optional_qr_code_data",
  "notes": "Starting shift at main gate"
}
```

**Response:**
```json
{
  "data": {
    "id": 456,
    "employee_id": 123,
    "check_in_time": "2025-01-15T09:00:00Z",
    "check_in_type": "manual",
    "latitude": 19.0760,
    "longitude": 72.8777,
    "address": "Mumbai, Maharashtra, India",
    "notes": "Starting shift at main gate",
    "employee": {
      "id": 123,
      "name": "John Security Guard"
    }
  },
  "message": "Employee checked-in successfully"
}
```

#### Check-out Employee
```http
POST /attendance/checkout
Content-Type: application/json
```

**Request Body:**
```json
{
  "attendance_id": 456,
  "latitude": 19.0760,
  "longitude": 72.8777,
  "notes": "Shift completed"
}
```

**Response:**
```json
{
  "data": {
    "id": 456,
    "employee_id": 123,
    "check_in_time": "2025-01-15T09:00:00Z",
    "check_out_time": "2025-01-15T21:00:00Z",
    "total_hours": 12.0,
    "overtime_hours": 4.0,
    "address": "Mumbai, Maharashtra, India",
    "notes": "Shift completed"
  },
  "message": "Employee checked-out successfully"
}
```

#### Attendance Reports
```http
GET /attendance/reports?from=2025-01-01&to=2025-01-31&employee_id=123
```

**Query Parameters:**
- `from` (date): Start date (required)
- `to` (date): End date (required)
- `employee_id` (integer): Filter by employee
- `client_id` (integer): Filter by client

**Response:**
```json
{
  "data": [
    {
      "date": "2025-01-15",
      "employee": {
        "id": 123,
        "name": "John Security Guard"
      },
      "check_in_time": "09:00:00",
      "check_out_time": "21:00:00",
      "total_hours": 12.0,
      "overtime_hours": 4.0,
      "status": "present" // present, absent, partial
    }
  ],
  "summary": {
    "total_days": 31,
    "present_days": 28,
    "absent_days": 3,
    "total_hours": 336.0,
    "overtime_hours": 112.0
  }
}
```

### 3. Payroll

#### Generate Payroll
```http
POST /payroll/generate
Content-Type: application/json
```

**Request Body:**
```json
{
  "period_start": "2025-01-01",
  "period_end": "2025-01-31",
  "tax_regime": "old", // old, new
  "employee_ids": [123, 124, 125], // optional, defaults to all active employees
  "override_attendance": false // if true, uses full month attendance
}
```

**Response:**
```json
{
  "data": {
    "payroll_run_id": "PR-2025-01-001",
    "period_start": "2025-01-01",
    "period_end": "2025-01-31",
    "tax_regime": "old",
    "total_employees": 3,
    "total_gross_salary": 75000,
    "total_deductions": 8250,
    "total_net_salary": 66750,
    "payslips": [
      {
        "id": 789,
        "employee_id": 123,
        "employee_name": "John Security Guard",
        "gross_salary": 25000,
        "net_salary": 22250,
        "pdf_url": "/api/payslips/789/pdf"
      }
    ],
    "created_at": "2025-01-31T23:59:59Z"
  },
  "message": "Payroll generated successfully for 3 employees"
}
```

#### List Payslips
```http
GET /payslips?period=2025-01&employee_id=123
```

**Query Parameters:**
- `period` (string): YYYY-MM format
- `employee_id` (integer): Filter by employee
- `page` (integer): Page number

**Response:**
```json
{
  "data": [
    {
      "id": 789,
      "employee_id": 123,
      "employee": {
        "id": 123,
        "name": "John Security Guard"
      },
      "period_start": "2025-01-01",
      "period_end": "2025-01-31",
      "basic_salary": 15000,
      "hra": 6000,
      "other_allowances": 4000,
      "gross_salary": 25000,
      "pf_deduction": 1800,
      "professional_tax": 300,
      "tax_deduction": 650,
      "total_deductions": 2750,
      "net_salary": 22250,
      "tax_regime": "old",
      "pdf_url": "/api/payslips/789/pdf",
      "created_at": "2025-01-31T23:59:59Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 1,
    "last_page": 1
  }
}
```

#### Download Payslip PDF
```http
GET /payslips/{id}/pdf
```

Returns PDF file with payslip details.

### 4. Finance & Invoicing

#### List Invoices
```http
GET /invoices?status=pending&client_id=1&from=2025-01-01&to=2025-01-31
```

**Query Parameters:**
- `status` (string): draft, pending, paid, overdue, cancelled
- `client_id` (integer): Filter by client
- `from` (date): Invoice date from
- `to` (date): Invoice date to
- `page` (integer): Page number

**Response:**
```json
{
  "data": [
    {
      "id": 101,
      "invoice_number": "INV-2025-001",
      "client": {
        "id": 1,
        "name": "Corporate Client Ltd"
      },
      "issued_date": "2025-01-01",
      "due_date": "2025-01-31",
      "status": "pending",
      "subtotal": 38500.00,
      "tax_amount": 6930.00,
      "total_amount": 45430.00,
      "paid_amount": 0.00,
      "outstanding_amount": 45430.00,
      "line_items_count": 2,
      "payments_count": 0,
      "created_at": "2025-01-01T00:00:00Z"
    }
  ],
  "summary": {
    "total_invoices": 15,
    "total_amount": 500000.00,
    "paid_amount": 300000.00,
    "outstanding_amount": 200000.00
  }
}
```

#### Get Single Invoice
```http
GET /invoices/{id}?include=line_items,payments
```

**Response:**
```json
{
  "data": {
    "id": 101,
    "invoice_number": "INV-2025-001",
    "client": {
      "id": 1,
      "name": "Corporate Client Ltd",
      "email": "client@corporate.com",
      "billing_address": "123 Business District, Mumbai"
    },
    "issued_date": "2025-01-01",
    "due_date": "2025-01-31",
    "status": "pending",
    "subtotal": 38500.00,
    "tax_amount": 6930.00,
    "total_amount": 45430.00,
    "paid_amount": 0.00,
    "outstanding_amount": 45430.00,
    "notes": "Security services for January 2025",
    "line_items": [
      {
        "id": 201,
        "description": "Security Guard Services - January",
        "quantity": 31,
        "unit_price": 1000.00,
        "tax_rate": 18.0,
        "subtotal": 31000.00,
        "tax_amount": 5580.00,
        "total": 36580.00
      },
      {
        "id": 202,
        "description": "Night Shift Premium",
        "quantity": 15,
        "unit_price": 500.00,
        "tax_rate": 18.0,
        "subtotal": 7500.00,
        "tax_amount": 1350.00,
        "total": 8850.00
      }
    ],
    "payments": [],
    "created_at": "2025-01-01T00:00:00Z"
  }
}
```

#### Create Invoice
```http
POST /invoices
Content-Type: application/json
```

**Request Body:**
```json
{
  "client_id": 1,
  "issued_date": "2025-01-01",
  "due_date": "2025-01-31",
  "notes": "Security services for January 2025",
  "line_items": [
    {
      "description": "Security Guard Services - January",
      "quantity": 31,
      "unit_price": 1000.00,
      "tax_rate": 18.0
    },
    {
      "description": "Night Shift Premium",
      "quantity": 15,
      "unit_price": 500.00,
      "tax_rate": 18.0
    }
  ]
}
```

**Response:**
```json
{
  "data": {
    "id": 101,
    "invoice_number": "INV-2025-001",
    // ... full invoice data
  },
  "message": "Invoice created successfully"
}
```

#### Record Payment
```http
POST /invoices/{id}/payments
Content-Type: application/json
```

**Request Body:**
```json
{
  "amount": 45430.00,
  "payment_method": "bank_transfer", // cash, bank_transfer, cheque, online
  "reference": "TXN123456789",
  "payment_date": "2025-01-15",
  "notes": "Payment received via NEFT"
}
```

**Response:**
```json
{
  "data": {
    "id": 301,
    "invoice_id": 101,
    "amount": 45430.00,
    "payment_method": "bank_transfer",
    "reference": "TXN123456789",
    "payment_date": "2025-01-15",
    "notes": "Payment received via NEFT",
    "created_at": "2025-01-15T12:00:00Z"
  },
  "message": "Payment recorded successfully"
}
```

### 5. Statutory Reports

#### GST Report
```http
GET /reports/gst?from=2025-01-01&to=2025-01-31&format=json
```

**Query Parameters:**
- `from` (date): Report period start (required)
- `to` (date): Report period end (required)
- `format` (string): json, csv

**Response (JSON):**
```json
{
  "data": {
    "period": {
      "from": "2025-01-01",
      "to": "2025-01-31"
    },
    "summary": {
      "total_taxable_value": 325000.00,
      "total_tax_amount": 58500.00,
      "total_invoice_value": 383500.00,
      "invoice_count": 15
    },
    "invoices": [
      {
        "invoice_number": "INV-2025-001",
        "client_name": "Corporate Client Ltd",
        "client_gstin": "27XXXXX1234X1Z5",
        "invoice_date": "2025-01-01",
        "taxable_value": 38500.00,
        "cgst": 3465.00,
        "sgst": 3465.00,
        "igst": 0.00,
        "total_tax": 6930.00,
        "invoice_value": 45430.00
      }
    ]
  }
}
```

**CSV Format Response:**
Returns CSV file with columns: Invoice Number, Client Name, Client GSTIN, Invoice Date, Taxable Value, CGST, SGST, IGST, Total Tax, Invoice Value

#### TDS Report
```http
GET /reports/tds?from=2025-01-01&to=2025-01-31&format=csv
```

Similar structure to GST report with TDS-specific fields.

#### PF Report
```http
GET /reports/pf?from=2025-01-01&to=2025-01-31
```

**Response:**
```json
{
  "data": {
    "period": {
      "from": "2025-01-01",
      "to": "2025-01-31"
    },
    "summary": {
      "total_employees": 50,
      "total_gross_salary": 1250000.00,
      "total_employee_contribution": 90000.00,
      "total_employer_contribution": 90000.00,
      "total_pf_contribution": 180000.00
    },
    "employees": [
      {
        "employee_id": 123,
        "employee_name": "John Security Guard",
        "uan": "123456789012",
        "gross_salary": 25000.00,
        "pf_basic": 15000.00,
        "employee_contribution": 1800.00,
        "employer_contribution": 1800.00,
        "pension_contribution": 1250.00,
        "admin_charges": 5.00
      }
    ]
  }
}
```

#### ESIC Report
```http
GET /reports/esic?from=2025-01-01&to=2025-01-31
```

Similar structure to PF report with ESIC-specific calculations.

#### Adhoc Reports
```http
POST /reports/adhoc
Content-Type: application/json
```

**Request Body:**
```json
{
  "report_type": "GST", // GST, TDS, PF, ESIC
  "period_start": "2025-01-01",
  "period_end": "2025-01-31",
  "format": "csv", // json, csv
  "email": "admin@agency.com" // optional, sends via email
}
```

**Response:**
```json
{
  "data": {
    "report_id": "RPT-2025-001",
    "report_type": "GST",
    "status": "processing", // processing, completed, failed
    "download_url": null, // available when completed
    "email_sent": false
  },
  "message": "Report generation initiated"
}
```

## Error Responses

### Validation Errors (422)
```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid.",
    "details": {
      "name": ["The name field is required."],
      "email": ["The email field must be a valid email address."]
    }
  }
}
```

### Authentication Errors (401)
```json
{
  "error": {
    "code": "UNAUTHENTICATED",
    "message": "Invalid API key or signature."
  }
}
```

### Authorization Errors (403)
```json
{
  "error": {
    "code": "FORBIDDEN",
    "message": "Insufficient permissions to access this resource."
  }
}
```

### Not Found Errors (404)
```json
{
  "error": {
    "code": "NOT_FOUND",
    "message": "The requested resource was not found."
  }
}
```

### Rate Limit Errors (429)
```json
{
  "error": {
    "code": "RATE_LIMIT_EXCEEDED",
    "message": "Too many requests. Please try again later.",
    "retry_after": 60
  }
}
```

### Server Errors (500)
```json
{
  "error": {
    "code": "INTERNAL_ERROR",
    "message": "An internal server error occurred. Please contact support.",
    "request_id": "req_1234567890"
  }
}
```

## Rate Limits

| Endpoint Category | Rate Limit | Window |
|------------------|------------|---------|
| Authentication | 5 requests | 1 minute |
| Employee Management | 100 requests | 1 minute |
| Attendance Operations | 200 requests | 1 minute |
| Payroll Operations | 50 requests | 1 minute |
| Finance Operations | 50 requests | 1 minute |
| Reports | 10 requests | 1 minute |

Rate limit headers are included in all responses:
```http
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1641024000
```

## Pagination

All list endpoints support pagination:

**Query Parameters:**
- `page` (integer): Page number (default: 1)
- `per_page` (integer): Items per page (default: 20, max: 100)

**Response Structure:**
```json
{
  "data": [...],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 100,
    "last_page": 5,
    "from": 1,
    "to": 20
  },
  "links": {
    "first": "/api/endpoint?page=1",
    "last": "/api/endpoint?page=5",
    "prev": null,
    "next": "/api/endpoint?page=2"
  }
}
```

## Webhooks

Configure webhooks to receive real-time notifications:

**Event Types:**
- `employee.created`
- `employee.updated`
- `attendance.checkin`
- `attendance.checkout`
- `payroll.generated`
- `invoice.created`
- `invoice.paid`

**Webhook Payload:**
```json
{
  "id": "evt_1234567890",
  "type": "employee.created",
  "created": 1641024000,
  "data": {
    "object": {
      // Employee object
    }
  }
}
```

**Webhook Verification:**
Verify webhook signatures using the same HMAC method as API authentication.

---

*API Documentation v1.0 - Last updated: January 2025*