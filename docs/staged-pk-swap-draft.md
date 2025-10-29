# Draft: Staged PK swap (integer id -> UUID)

This document outlines a draft PR to flip the central `tenants` table from integer PK to UUID PK in staged, safe phases.

Goal
- Make `uuid` the canonical tenant identifier used by stancl/tenancy and by the application while keeping the migration non-destructive and reversible.

Phases (high level)

1) Preliminaries (already applied in earlier work)
- Add `uuid` (string, 36) and `data` JSON to `tenants`, backfill UUIDs for existing rows.
- Add nullable `tenant_uuid` columns to all tenant-scoped tables and backfill them from central `tenants` by `tenant_id`.
- Keep integer `tenant_id` columns intact and keep existing FKs unchanged.

2) Application sweep
- Update all write paths to set `tenant_uuid` alongside `tenant_id` when creating/updating tenant-scoped rows (controllers, observers, jobs, factories, seeders, console commands, admin controllers).
- Add model `$fillable` and PHPDoc hints where needed so static analysis is happy.
- Add tests that verify new resources have both `tenant_id` and `tenant_uuid` populated.

3) Add constraints and indexes (non-blocking)
- Add indexes on `tenant_uuid` where appropriate (e.g., tenant_scoped tables).
- Add FK-like constraints where supported (optionally use application enforced checks) referencing a central tenants.uuid column. Do NOT drop integer FKs yet.

4) Final PK flip (careful, high-risk)
- Add migration that:
  - Adds a new UUID primary key column if not already present (or uses existing `uuid`).
  - Repoint references to use `tenant_uuid` columns (requires application update and careful migration of FK constraints).
  - Drop integer `id` PK only after a maintenance window and after thorough verification.
- Include database-specific SQL for MySQL/Postgres (UUID functions differ) and an explicit rollback path that recreates integer PK if something goes wrong.

Rollback plan
- Each migration must be reversible: if final PK flip is risky, perform it behind a feature flag / maintenance window.
- Keep integer `tenant_id` columns until after rollback window has passed and all code is confirmed to use `tenant_uuid`.

Checklist for PR
- [ ] All migrations added and tested locally on sqlite and a local MySQL/Postgres dev instance.
- [ ] Application changes to write `tenant_uuid` everywhere and tests added.
- [ ] PHPStan and test suite green.
- [ ] Migration runbook with exact SQL, downtime windows, and rollback steps.
- [ ] DB backups + export steps and verification queries to validate `tenant_uuid` population.

How to test locally
- Run `php artisan migrate` against a local database (MySQL/Postgres) with a snapshot of production schema.
- Run phpunit and phpstan.
- Manually verify `tenant_uuid` presence and that stancl/tenancy resolves tenants by uuid.

Notes
- This draft PR file is intentionally non-invasive. The actual heavy lifting (final PK switch) must be scheduled and applied during a maintenance window with backups and monitoring.

Suggested reviewers: @db-admin, @backend-lead, @devops

Commands to run during PR validation

```bash
# run tests and static analysis
composer test
vendor/bin/phpstan analyse -c phpstan.neon app --memory-limit=1G

# run migrations locally (use your DB of choice)
php artisan migrate --seed
```
