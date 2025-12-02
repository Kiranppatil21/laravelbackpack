# AI Agent Instructions

## Architecture Overview
This is a Laravel 12 multi-tenant SaaS backend using `stancl/tenancy` for database separation. Central DB handles tenant metadata (`tenants`, `tenant_subscriptions`, `razorpay_payments`). Each tenant gets its own database with domain-specific tables (agencies, clients, employees, attendance, payroll, invoices).

**Key pattern**: Tenant model uses UUID as primary key (`app/Models/Tenant.php`) while keeping integer `id` for FK compatibility.

## Multi-Tenancy Patterns
- **Tenant migrations**: Use `database/migrations/tenant/` for tenant-scoped schema changes (NOT central migrations)
- **Tenant identification**: By domain via `InitializeTenancyByDomain` middleware in `routes/tenant.php`  
- **Bootstrappers**: Database, cache, filesystem, and queue tenancy are configured in `config/tenancy.php`
- **Testing**: Most tests use central DB (sqlite :memory:) — tenant context may need special setup

## Critical Developer Workflows
```bash
# Setup with tenant migrations
composer install && cp .env.example .env && php artisan key:generate
php artisan migrate --seed  # Central DB only
# Tenant migration handling is via stancl/tenancy commands

# Development with concurrent processes
composer run-script dev  # Serves app + queue listener + logs + vite

# Testing patterns  
composer test  # Uses sqlite :memory: + sync queues
php artisan test --filter SpecificTest
```

## External Service Integration Patterns
- **Payment SDKs**: `app/Services/RazorpayResolver.php` checks container bindings first, then instantiates with config
- **In tests**: Bind fakes to container under SDK class names: `app()->instance('\\Razorpay\\Api\\Api', $fake)`
- **Visitor API**: Dual auth via `VerifyVisitorApiKey` middleware — accepts API key OR HMAC signatures

## Role-Based Access Control (Spatie)
- Seven roles: Super Admin, Agency Owner, HR, Client, Guard/Employee, Visitor, Police
- Seeding uses `firstOrCreate` patterns in `database/seeders/RoleSeeder.php`
- Permissions: `manage users`, `create listings`, `edit listings` 

## Testing & CI Secrets
- **Required for CI**: At least one of `VISITOR_API_KEY` or `VISITOR_HMAC_SECRET`
- **Queue processing**: CI runs background `php artisan queue:work` for notification tests
- **Cypress**: ESM config, uses `CYPRESS_RECORD_KEY` for dashboard recording

## Project-Specific Conventions
- **Controllers**: Organized by context (`Admin/`, `Api/`, `Auth/`)
- **Middleware**: Custom `AllowRole`, `CheckIfAdmin`, `VerifyVisitorApiKey`  
- **External SDK testing**: Always bind fakes to exact class string used by resolvers
- **Migration safety**: Never reorder existing migrations; tenant schema changes go in `tenant/` directory

## Key Files for Context
- `config/tenancy.php` — tenancy configuration and bootstrappers
- `database/migrations/tenant/` — all tenant-scoped schema (agencies → invoices)
- `app/Services/RazorpayResolver.php` — container-first SDK resolution pattern
- `app/Http/Middleware/VerifyVisitorApiKey.php` — dual API auth implementation
- `scripts/sign-*.{js,py}` — HMAC signing helpers for visitor API testing
