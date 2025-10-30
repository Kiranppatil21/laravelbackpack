# laravelbackpack
<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

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
