# Project Documentation — laravelbackpack

This document is an in-depth developer guide for the laravelbackpack repository. It collects the essential architecture, operational commands, conventions, and troubleshooting steps needed to be productive.

Contents (quick)
- Project summary
- Architecture & data flow
- Tenancy specifics (stancl/tenancy)
- Key files & directories to open
- Local development: backend and frontend commands
- Tests and CI
- Conventions & patterns
- External integrations
- Troubleshooting & common gotchas
- Next steps and how to ask for help

---

## Project summary
- Type: Laravel 12 backend (multi-tenant)
- Purpose: Manage Agencies, Clients, Employees, Attendance, Payroll, Invoices and visitor check-ins (kiosk/IoT).
- Tenancy model: Central DB holds tenant metadata; each tenant has its own database (or schema) for domain data.


## Architecture & data flow

High-level
- Central application (single Laravel app) runs with Stancl Tenancy (tenancyforlaravel v3). Central DB contains tenants, tenant_subscriptions and other central tables (example: `razorpay_payments`).
- When a tenant is provisioned a tenant DB is created and tenant migrations run. Tenant tables hold domain entities: `agencies`, `clients`, `employees`, `attendance`, `payrolls`, `invoices`.

Request flows
- Public API (example): `POST /api/visitors/checkin` — this endpoint can be protected by either an API key header or an HMAC signed payload. The middleware `app/Http/Middleware/VerifyVisitorApiKey.php` enforces this.
- Auth-protected API: routes under `routes/api.php` guarded by `auth:sanctum`. Example resource endpoints: `agencies`, `clients`, `employees`.
- Tenant routes: `routes/tenant.php` are loaded through the tenant route provider and are initialized with tenancy middleware (domain-based initialization in this repo).


## Tenancy specifics

Where configured
- `config/tenancy.php` — shows the bootstrappers used (Database, Cache, Filesystem, Queue). The file contains the migration & seeder parameters used by `tenants:migrate` and `tenants:seed`.

Key points
- Tenancy uses Stancl's UUID id generator in this repo (see `id_generator` config). Tenant DB names are created with the `prefix` setting (`tenant` by default).
- Bootstrappers applied: DatabaseTenancyBootstrapper, CacheTenancyBootstrapper, FilesystemTenancyBootstrapper, QueueTenancyBootstrapper. This makes storage paths, cache, queues and DB tenant-scoped.
- Tenant migrations live in `database/migrations/tenant/`. Add tenant schema changes there.

Useful artisan commands (stancl tenancy)
- Run tenant migrations for all tenants:
```sh
php artisan tenants:migrate
```
- Seed tenant DBs (uses `DatabaseSeeder` by default):
```sh
php artisan tenants:seed
```

Note: `config/tenancy.php` sets migration parameters (`--path => database/migrations/tenant`) and `--force => true` for production runs. If your environment needs a custom flow, inspect `app/Console/Commands` for project-specific CLI wrappers.


## Key files & directories to inspect (quick map)
- `README.md` — high-level notes (billing tests, signing helpers), local tips.
- `config/tenancy.php` — tenancy configuration (most important file to understand tenancy behavior).
- `routes/api.php` — main API surface (auth, visitor checkin, finance endpoints).
- `routes/tenant.php` — tenant-scoped routes (InitializeTenancyByDomain middleware used).
- `database/migrations/tenant/` — tenant migrations (add/change tenant schema here).
- `database/seeders/RoleSeeder.php` — Spatie roles & permissions seeding example.
- `scripts/sign-node.js`, `scripts/sign-python.py` — HMAC signing helpers used by visitor endpoints.
- `app/Http/Middleware/VerifyVisitorApiKey.php` — visitor API protection (headers/HMAC validation).
- `app/Http/Controllers/Api/*` — controllers implementing API handlers (AuthController, VisitorController, FinanceController, etc.).
- `package.json` — asset tooling (Vite), Cypress scripts, `type: "module"` (ESM).
- `.github/workflows/*` — CI jobs: see how tests, Cypress, and queue worker are started.


## Local development — backend

Minimum steps
1. Install PHP dependencies and copy env:
```sh
composer install
cp .env.example .env
php artisan key:generate
```
2. Configure DB connections in `.env` (central connection and optionally template connection). Example: `DB_CONNECTION=central` and credentials.
3. Run central migrations & seed:
```sh
php artisan migrate --seed
```
4. (Create a tenant) Use the app's provisioning endpoint or seed a tenant record.
5. Run tenant migrations & seeders:
```sh
php artisan tenants:migrate
php artisan tenants:seed
```
6. Start the app:
```sh
php artisan serve --port=8000
```
7. (Optional) Start a queue worker (CI uses this; do the same locally for parity):
```sh
php artisan queue:work --driver=database
```

Notes & tips
- If tenant DB creation should create a DB user per tenant, switch to the `PermissionControlledMySQLDatabaseManager` in `config/tenancy.php` and configure grants.
- If you need to debug tenant initialization, add log points in `app/Providers` or check which middleware runs by reading `routes/tenant.php`.


## Local development — frontend / assets

Tooling
- Vite + React + Tailwind are used. `package.json` uses `type: "module"`, so `cypress.config.js` is ESM.

Commands
```sh
npm install
npm run dev   # start Vite dev server (HMR)
npm run build # production build (Vite)
```

Cypress
- Interactive: `npm run cypress:open` (or `npx cypress open`)
- Headless (CI): `npm run cypress:run` or `npx cypress run --spec "cypress/e2e/**"`

Troubleshooting
- If Cypress cannot reach the server, ensure `php artisan serve --port=8000` is running and `cypress.config.js` baseUrl matches.


## Tests & CI

Unit & Feature tests
- Run PHP tests with PHPUnit/Laravel wrapper:
```sh
php artisan test
```

E2E
- Cypress specs under `cypress/e2e`.
- The repo uses a GitHub Actions job to run Cypress; to enable parallelization/recording add `CYPRESS_RECORD_KEY` as a secret.

CI important envs & steps (scan `.github/workflows/*`)
- `VISITOR_API_KEY` or `VISITOR_HMAC_SECRET` — CI requires at least one for visitor signing checks.
- CI spins up a background `php artisan queue:work` (database driver) to process queued jobs used in tests.


## Conventions & patterns

- Migrations: tenant migrations go into `database/migrations/tenant/` (do not swap tenant migrations with central migrations).
- Seeders: prefer idempotent patterns (`firstOrCreate`) — see `database/seeders/RoleSeeder.php`.
- Permissions: Spatie Permission package is used. Roles include: `Super Admin`, `Agency Owner`, `HR`, `Client`, `Guard/Employee`, `Visitor`, `Police`.
- External SDKs (Razorpay/Stripe): controllers resolve SDKs from the container. Tests bind fakes into the container to avoid real network calls — see README example for binding `\Razorpay\Api\Api`.
- Cypress config: keep `export default` form (ESM) when editing `cypress.config.js`.


## External integrations

Razorpay & Stripe
- Code expects SDKs to be resolved from the container. In tests, bind a fake object under `\Razorpay\Api\Api` or `Stripe\StripeClient::class` to mimic behavior.

Visitor signing
- The visitor API supports two protection modes: API key header `X-VISITOR-API-KEY` or HMAC with `X-VISITOR-TIMESTAMP` and `X-VISITOR-SIGNATURE`.
- Signature construction (described in README): `HMAC-SHA256(timestamp + '|' + raw_body, VISITOR_HMAC_SECRET)`.
- Helper scripts: `scripts/sign-node.js` and `scripts/sign-python.py` — use them to generate valid signatures for testing.


## Troubleshooting & common gotchas

- CI failures related to visitor signing: Ensure `VISITOR_API_KEY` or `VISITOR_HMAC_SECRET` secrets are set in Actions. Locally the middleware is permissive when those env vars are unset.
- Cypress cannot reach server: verify `php artisan serve` is running and `cypress.config.js` baseUrl matches, or use `CYPRESS_BASE_URL` env var when running.
- Tenant migrations not applied: confirm you ran `php artisan tenants:migrate` and that `config/tenancy.php` `migration_parameters` point to `database/migrations/tenant` with `--realpath => true`.
- Queue-dependent tests failing: start `php artisan queue:work --driver=database` as CI does.
- Editing Cypress config: remember `package.json` uses `type: "module"` — keep ESM style.


## Next steps & maintenance suggestions

- Add a short `README.DEV.md` or `docs/DEVELOPER_QUICKSTART.md` that contains the minimal copy-paste commands and an optional Makefile to wrap common flows.
- Add example tenant provisioning scripts or test helpers that create a tenant and run migrations in one command for local dev.
- Consider a short `docs/TENANCY.md` that documents tenant lifecycle (create DB, migrate, seed, delete) with concrete artisan commands and examples.


## Where I looked to build this document
- `README.md`
- `config/tenancy.php`
- `routes/api.php`
- `routes/tenant.php`
- `database/seeders/RoleSeeder.php`
- `package.json`
- `docs/CYPRESS.md`
- `scripts/sign-node.js`, `scripts/sign-python.py`


---

If you'd like, I can:
- Expand this into a one-page `README.DEV.md` tuned for new developers with precise `.env` examples.
- Create quick helper scripts / Makefile entries for `tenants:migrate` + seed + create-demo-tenant.
- Walk through one endpoint end-to-end and annotate the controller, middleware and model files.

Tell me which of the above you'd like next and I will implement it.
