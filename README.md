# 🚀 SecureServe SaaS - Employee Management System

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4.svg)](https://php.net)
[![Backpack](https://img.shields.io/badge/Backpack-6.8.6-orange.svg)](https://backpackforlaravel.com)

## 📋 Overview

SecureServe is a comprehensive multi-tenant SaaS application for security services management, built with Laravel Backpack. Perfect for security agencies managing employees, clients, attendance, and payroll across multiple locations.

## ✨ Key Features

- 🏢 **Multi-tenant Architecture** - Complete database separation per tenant
- 👥 **Employee Management** - CRUD operations with client assignments
- 📅 **Advanced Attendance System** - Bulk operations with shift tracking (S1, S2, S3)
- ⏰ **Overtime Management** - Built-in OT tracking and calculations  
- 💰 **Payroll Processing** - Automated salary calculations and payslips
- 🏬 **Client Management** - Multiple site locations and contracts
- 📊 **Comprehensive Reporting** - Attendance reports and invoicing
- 🔐 **Role-Based Access Control** - 7 user roles with granular permissions
- 📱 **Responsive Interface** - Works seamlessly on all devices
- 🎨 **Modern UI** - Clean, intuitive design with Bootstrap styling

## 🎯 Perfect For

- Security service companies
- Guard service providers  
- Employee management agencies
- Multi-location businesses
- Any organization needing robust attendance tracking

## 🚀 Quick Start

### One-Command Setup
```bash
git clone https://github.com/Kiranppatil21/laravelbackpack.git
cd laravelbackpack
git checkout ci/run-rbac-rerun
./transfer-setup.sh
php artisan serve
```

### Manual Installation
```bash
# 1. Clone repository
git clone https://github.com/Kiranppatil21/laravelbackpack.git
cd laravelbackpack
git checkout ci/run-rbac-rerun

# 2. Install dependencies
composer install
npm install

# 3. Environment setup
cp .env.example .env
php artisan key:generate

# 4. Database setup
touch database/database.sqlite
php artisan migrate --seed

# 5. Build assets
npm run build

# 6. Start development server
php artisan serve
```

## 🔑 Default Access Credentials

- **Admin Panel**: http://localhost:8000/admin
- **Username**: admin@example.com
- **Password**: password123

## 📋 System Requirements

- **PHP**: 8.1 or higher with SQLite extension
- **Composer**: Latest version
- **Node.js**: 16+ with NPM
- **Database**: SQLite (default) or MySQL/PostgreSQL
- **Web Server**: Apache/Nginx (for production)

## 🏗️ Architecture Overview

```
SecureServe SaaS Architecture
├── 🏢 Multi-tenant Database Separation
├── 👥 Employee Management Module
├── 📅 Bulk Attendance System
├── 💰 Payroll Processing Engine
├── 🏬 Client & Site Management
├── 📊 Reporting & Analytics
└── 🔐 Security & Access Control
```

## 🛠️ Technology Stack

- **Framework**: Laravel 12
- **Admin Panel**: Backpack CRUD 6.8.6
- **Frontend**: Blade Templates + Bootstrap 5
- **Database**: SQLite with multi-tenancy support
- **Authentication**: Laravel Breeze + Spatie Permissions
- **Multi-tenancy**: stancl/tenancy package
- **Payment Processing**: Razorpay integration
- **Asset Building**: Vite + NPM

## 📖 Documentation

- 📋 [Transfer Guide](TRANSFER_GUIDE.md) - Setup instructions for new systems
- 🛠️ [Troubleshooting Guide](TROUBLESHOOTING.md) - Common issues and solutions
- 🔧 [Development Notes](README.DEV.md) - Advanced configuration options

## 🎭 User Roles & Permissions

- **Super Admin** - Full system access
- **Agency Owner** - Tenant management
- **HR Manager** - Employee and payroll management  
- **Client** - View assigned employees and reports
- **Security Guard** - Basic attendance and profile access
- **Supervisor** - Team management capabilities
- **Police/Visitor** - Limited access for verification

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

Developer notes — testing billing flows locally
------------------------------------------------
If you want to run the end-to-end signup → billing → webhook activation flow locally (without calling real Razorpay/Stripe), you can bind fakes into the container.

Example: bind a fake Razorpay SDK in a test or `php artisan tinker`:

```php
// in a test
$fake = new class {
	public $order;
	public function __construct()
	{
		$this->order = new class {
			public function create($payload) { return ['id' => 'order_test_1', 'amount' => $payload['amount']]; }
			public function fetch($id) { return ['id' => $id, 'receipt' => '1']; }
		};
	}
};
app()->instance('\\Razorpay\\Api\\Api', $fake);
```

For Stripe, bind a fake `\\Stripe\\StripeClient` implementation (or a minimal stub) to `Stripe\\StripeClient::class` so the signup controller and checkout creation code can run in tests.

Running the end-to-end test locally:

```bash
php artisan test --filter SignupProvisioningE2ETest
```

Notes
- `RazorpayResolver` prefers container-bound instances, so binding the SDK under `Razorpay\\Api\\Api` will be picked up by the resolver during tests.
- In CI, ensure the appropriate env vars are set or tests bind fakes for external SDKs.

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Phase 1 — Project setup & ERD (status)

This repository implements the backend foundations for the Phase 1 plan. Below is a short checklist and current status.

- Laravel 12 backend: Present (see `composer.json`).
- React frontend: Not scaffolded yet — not present in `package.json`.
- Database migrations: Present for Agencies, Clients, Employees, Attendance, Payroll, Invoices (see `database/migrations` and `database/migrations/tenant`).
- Auth scaffolding (Breeze/Jetstream): Not installed (you can install Breeze to scaffold a React frontend with auth).
- Tailwind: Present in `package.json` devDependencies.
- Roles: Spatie permission included; `RoleSeeder` seeds `Super Admin` and `Agency Owner` plus additional roles (HR, Client, Guard/Employee, Visitor, Police).

## Cypress end-to-end tests

For details on running the Cypress end-to-end tests, stable test selectors used in the app, fixtures, and helper scripts, see `docs/CYPRESS.md`.

Run the primary employee-create spec locally with:

```bash
npx cypress run --spec "cypress/e2e/employee_create.cy.js"
```

Simple ERD (text-based)

Tenants/Central vs Tenant DBs

Central DB tables:
- tenants (id)
- tenant_subscriptions (tenant_id)
- razorpay_payments

Tenant DB (per-tenant) tables (examples):
- agencies (id)
- clients (id, agency_id)
- employees (id, agency_id)
- attendance (employee_id)
- payrolls (employee_id)
- invoices (client_id)

Relationships (high level):
- Agency 1---* Client
- Agency 1---* Employee
- Employee 1---* Attendance
- Employee 1---* Payroll
- Client 1---* Invoice
- Tenant 1---* TenantSubscription

If you want, I can add a visual ERD (SVG/PNG) under `docs/erd.png` or generate a dbdiagram.io link.
