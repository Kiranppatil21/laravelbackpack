# PR: Phase 3 — tenant_uuid index rollout (notes)

This patch adds a migration to create non-unique indexes on `tenant_uuid` for tenant-scoped tables.

Why
---
- After backfilling `tenant_uuid` for tenant-scoped rows (clients, agencies, employees, invoices, domains), creating indexes on `tenant_uuid` improves query performance for tenant-scoped queries and supports the rollout of tenant UUIDs.

What changed
-----------
- Added migration: `database/migrations/2025_10_30_110000_add_tenant_uuid_indexes.php`
  - Adds non-unique indexes named `<table>_tenant_uuid_index` on `tenant_uuid` for the following tables: `clients`, `agencies`, `employees`, `invoices`, `domains`.
  - Migration is defensive (wrapped in try/catch) so it is safe to re-run in environments where indexes may already exist.

Files of interest
-----------------
- `database/migrations/2025_10_30_110000_add_tenant_uuid_indexes.php` — migration that adds/removes the indexes
- `tools/backfill-samples/` — contains JSON backups created during the Phase 3 backfill remediation (pre-change backups are present for clients/agencies/employees/invoices). Review these before applying similar changes in other environments.

How to run locally (safe)
-------------------------
1. Run migrations (will create the indexes):

```bash
php artisan migrate
```

2. Verify the tenant_uuid backfill status:

```bash
php artisan tenant:verify-uuids
```

Rollback guidance
-----------------
- The migration's `down()` drops the created indexes. If you want to fully revert the remediation (restore previous `tenant_uuid` values), restore the JSON backups found under `tools/backfill-samples/` into your DB before running `down`.

Canary / Production
-------------------
- Before running migrations or remediation in production: take a full DB backup / snapshot.
- Run the remediation on a production snapshot and re-run verification.
- If snapshot validation succeeds, plan a short canary window (1-2 tenants) and monitor application behavior (queries, errors, background jobs) for ~30-60 minutes.

Notes
-----
- The migration is intentionally non-destructive and idempotent (wrapped in try/catch) to work across different schemas.
- We set `tenant_uuid` values directly in the local snapshot remediation because `tenants.id` in this schema is a UUID string while other tables use numeric `tenant_id` columns; writing UUIDs into numeric `tenant_id` would truncate or fail.
