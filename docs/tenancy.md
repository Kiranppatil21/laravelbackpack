# Tenancy (Subdomain) — Local development guide

This project uses stancl/tenancy for multi-tenant support. The app is configured to initialize tenancy by domain and subdomain via the `InitializeTenancyByDomain` and `InitializeTenancyBySubdomain` middleware (see `app/Providers/TenancyServiceProvider.php`).

This document contains quick local-development instructions to exercise subdomain-based tenancy (agency1.example.test).

Important: there are two tenancy modes in this repo:
- Central DB: the central database stores tenant records (`tenants`, `tenant_subscriptions`, billing data) and admin UI runs from the central DB.
- Per-tenant DB: stancl/tenancy provisions tenant databases for tenant-scoped application data. The middleware initializes tenancy automatically when a request matches a known domain/subdomain.

I. Quick local setup (macOS)

Option A — Laravel Valet (recommended for macOS)
1. Install Valet (if not installed):
   - `composer global require laravel/valet` and `valet install`
2. Park or link your project directory:
   - `cd /Users/admin/Desktop/laravelbackpack` and `valet park` (or `valet link laravelbackpack`)
3. Use a TLD Valet manages (like `.test`). Choose a subdomain host like `example.test`.
4. Add tenant domains (example):
   - `http://acme.example.test` or `http://tenant1.example.test` will resolve to your local app via Valet.

Option B — /etc/hosts (manual, works without Valet)
1. Edit `/etc/hosts` (requires sudo):

   ```bash
   sudo nano /etc/hosts
   # add lines like:
   127.0.0.1   acme.example.test
   127.0.0.1   tenant2.example.test
   ```

2. Use your local PHP server or Valet to serve the app. Requests to those hostnames will reach your app running on 127.0.0.1.

II. Create a tenant (central) and map domain

The repo contains an admin CRUD for tenants (Backpack) and a `CreateTenant` artisan command to create tenants + domain records. Two ways:

A. Admin UI
- Visit `http://admin.test` (or the Backpack url) and create a new tenant via the `Tenants` CRUD. Provide the `domain` field (for example `acme.example.test`).

B. Artisan command
- There's an artisan command at `app/Console/Commands/CreateTenant.php` which creates a stancl/tenant and a Domain record. Example:

```bash
php artisan app:create-tenant "ACME Inc" acme.example.test
```

III. Verify tenancy initialization

- Request a route defined in `routes/tenant.php` using the tenant domain, e.g. `http://acme.example.test/tenant/ping` (the repo has a sample tenant route). If tenancy initializes correctly you should see a tenant-specific response.

- The middleware ordering is adjusted in `app/Providers/TenancyServiceProvider.php` (it prepends the initialization middleware to the kernel middleware priority). This ensures tenancy initializes early in the request lifecycle.

IV. Notes and troubleshooting

- If you see central-admin pages instead of tenant pages, check that the domain exists in the `domains` table (stancl/tenancy Domain model) and points to the correct tenant id.
- Local dev DB vs per-tenant DB: in test and local environments we sometimes use central DB-only flows for simplicity (the code contains fallbacks). When switching to full per-tenant DB flows, ensure your tenancy database managers are configured in `config/tenancy.php`.
- When tests create tenants they may use the central `tenants` table directly; be mindful of the stancl tenant model differences (UUID vs integer ids) if you refactor migrations.

V. Quick checklist for local testing
- [ ] Add host entries (Valet or `/etc/hosts`).
- [ ] Create tenant via admin or artisan command.
- [ ] Visit tenant domain and verify tenant routes load.
- [ ] Trigger billing signup flows on tenant domain to verify webhooks and tenant activation.

If you'd like, I can add a small `Makefile` with convenience targets (eg. `make valet-setup`) or provide an optional `docker-compose` recipe to emulate DNS with Traefik for subdomains.