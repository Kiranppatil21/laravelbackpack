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

## Rollback steps, verification scripts and quick checks

Add the following to the PR runbook so an on-call / responder can run quick verification and rollback steps if something goes wrong.

### Verification queries (quick sanity checks)

Run these before and after the backfill to confirm tenant_uuid coverage and correctness.

-- Count rows missing tenant_uuid in tenant-scoped tables
```sql
SELECT 'clients' AS tbl, COUNT(*) AS missing FROM clients WHERE tenant_uuid IS NULL
UNION ALL
SELECT 'agencies', COUNT(*) FROM agencies WHERE tenant_uuid IS NULL
UNION ALL
SELECT 'employees', COUNT(*) FROM employees WHERE tenant_uuid IS NULL
UNION ALL
SELECT 'invoices', COUNT(*) FROM invoices WHERE tenant_uuid IS NULL
UNION ALL
SELECT 'domains', COUNT(*) FROM domains WHERE tenant_uuid IS NULL;
```

-- Spot-check tenant_uuid matches central tenants table for a sample tenant_id
```sql
SELECT c.id, c.name, c.tenant_id, c.tenant_uuid, t.uuid AS expected_uuid
FROM clients c
LEFT JOIN tenants t ON t.id = c.tenant_id
WHERE c.tenant_id = 1
LIMIT 50;
```

-- Ensure no rows reference a tenant_id that does not exist in tenants
```sql
SELECT COUNT(*) FROM clients c WHERE NOT EXISTS (SELECT 1 FROM tenants t WHERE t.id = c.tenant_id);
```

### Backfill run (example approach)

1. Create a job that updates rows in batches (e.g., 5k-50k rows per job) to set `tenant_uuid` = lookup(tenants.id).
2. Run on a production snapshot or a small canary subset first.
3. Monitor the verification queries above after each batch.

Example SQL used inside each batch (psuedocode / adapt to your DB and code path):

```sql
UPDATE clients c
SET tenant_uuid = (SELECT t.uuid FROM tenants t WHERE t.id = c.tenant_id)
WHERE c.tenant_uuid IS NULL
AND c.id BETWEEN :start_id AND :end_id;
```

Run the UPDATE in small chunks and commit between chunks; log rows updated and errors. If you use Laravel jobs, ensure each job is idempotent and re-runnable.

### Index addition (post-backfill)

After the backfill is complete and verified, add non-unique indexes on `tenant_uuid` for all tenant-scoped tables to accelerate queries. Example (MySQL):

```sql
ALTER TABLE clients ADD INDEX idx_clients_tenant_uuid (tenant_uuid);
ALTER TABLE agencies ADD INDEX idx_agencies_tenant_uuid (tenant_uuid);
-- etc.
```

Add indexes in a separate migration and deploy during a regular release (no maintenance required). Monitor replica lag and long-running queries when adding indexes on large tables.

### Rollback steps (if a problem is detected during/after backfill)

1. Pause the backfill/job queue and stop any ongoing batch jobs.
2. If data corruption is observed (unlikely if you only set tenant_uuid based on tenants table):
  - Restore from the latest DB backup taken before starting the backfill.
  - If you captured per-batch logs, you may be able to reverse individual batches by setting `tenant_uuid = NULL` for affected id ranges.

3. If the application starts failing after merging code that reads `tenant_uuid` but some rows were missed: revert the application changes (rollback the release) and switch traffic to the previous release.

4. If the final PK flip was attempted and failed, follow the detailed DB rollback steps outlined in the “Final PK flip” section (this is DB specific—prepare a tested SQL script that recreates the integer PK and restores FKs from backups).

### Automation / Monitoring suggestions

- Add a small script to run verification queries periodically during the backfill and emit metrics (counts missing tenant_uuid, rows updated/sec). Example script (psuedocode):

```bash
# simple loop: run verification and log
php artisan tinker --execute="echo \"`date` - missing client uuids: \" . DB::table('clients')->whereNull('tenant_uuid')->count() . PHP_EOL;"
```

- Emit results to your monitoring/alerting (Datadog/Prometheus) and create an alert if the missing count increases or plateaus unexpectedly.

### Decision guidance: Merge now vs keep draft

Recommendation: keep the PR as draft until the client API tests are added and green (we just added them), and until you've run a canary backfill on a production snapshot. Merging the code now is low-risk if the code is deployed behind a feature flag and you do NOT run the backfill yet — but the safest path is:

1. Add and run client API tests (done in this PR).
2. Run a canary backfill on a production snapshot and verify using the SQL queries above.
3. Add indexes post-backfill in a separate migration.
4. Schedule the final PK flip with DB team and a rollback-tested plan.

If you need me to, I can add the small monitoring script and a simple artisan command that runs the verification queries and prints a short summary.
