# AI Agent Instructions

This repo is a Laravel 12 multi-tenant SaaS backend using stancl/tenancy. The central database stores tenant metadata and billing; each tenant receives its own database for domain data (agencies, clients, employees, attendance, payroll, invoices).

## Big picture
- Central DB: tenant registry, subscriptions, payments (tables: `tenants`, `tenant_subscriptions`, `razorpay_payments`).
- Tenant DBs: tenant-scoped data created/migrated separately via stancl/tenancy CLI.
- Routes: tenant-scoped routes are in `routes/tenant.php` and protected by tenancy middleware.

## Key files & places to inspect
- `app/Models/Tenant.php` — UUID primary key pattern (keeps integer `id` for FK compatibility).
- `config/tenancy.php` — tenancy bootstrappers (database, cache, filesystem, queues).
- `routes/tenant.php` — tenancy initialization (uses `InitializeTenancyByDomain`).
- `database/migrations/tenant/` — tenant-scoped migrations (do not place tenant schema in central migrations).
- `app/Services/RazorpayResolver.php` — external SDK resolver pattern (container-first).
- `app/Http/Middleware/VerifyVisitorApiKey.php` — visitor API dual-auth (API key or HMAC).
- `database/seeders/RoleSeeder.php` — Spatie roles/permissions seeding examples.
- `scripts/` — signing helper scripts (e.g., `sign-*.py`, `sign-*.js`) used in visitor API tests.

## Developer workflows (examples)
```bash
# Local setup (central DB only)
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed    # central DB only

# Tenant migrations (stancl/tenancy)
# e.g. php artisan tenants:migrate

# Development (starts frontend, queues, vite via helper)
composer run-script dev

# Tests
composer test
php artisan test --filter SpecificTest

# Background worker
php artisan queue:work --tries=3
```

## Project-specific patterns & conventions
- Tenancy: keep tenant schema under `database/migrations/tenant/` and run via stancl/tenancy commands.
- Tenant model: `app/Models/Tenant.php` uses UUIDs for tenant identity but preserves integer `id` for compatibility.
- SDK resolution: resolvers (like `app/Services/RazorpayResolver.php`) check the container first — tests inject fakes with the exact class string:

```php
app()->instance('\\Razorpay\\Api\\Api', $fake);
```

- Visitor API: dual-auth implemented in `app/Http/Middleware/VerifyVisitorApiKey.php`; use the `scripts/sign-*` helpers to produce HMAC-signed test requests.
- Controllers are grouped by context (e.g., `Admin/`, `Api/`, `Auth/`).

## CI & test notes
- CI requires at least one of `VISITOR_API_KEY` or `VISITOR_HMAC_SECRET` for visitor-system tests.
- Many central tests use sqlite :memory:. Tenant-context tests require creating/bootstrapping tenants before running tenant-scoped assertions.
- CI/test suites run background queue workers for notification tests; mirror with `php artisan queue:work` when debugging.

## Safe change checklist
- For tenant schema changes: add migration to `database/migrations/tenant/` and run via tenancy migration commands — avoid changing central migrations.
- When adding external SDKs, follow the resolver/container-binding pattern so tests can inject fakes.

If you want more examples (sample tenant migration, a mock Razorpay fake, or visitor HMAC request examples), tell me which area to expand.
