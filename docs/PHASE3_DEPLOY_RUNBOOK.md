# Phase 3 — Deploy & Canary Runbook

This runbook describes how to safely apply the `tenant_uuid` index migration and verify the Phase 3 rollout in staging and production.

Preconditions
- Ensure you have a full DB backup/snapshot for the target environment.
- Confirm you have deploy/run permissions on the target environment (SSH, CI, or platform access).
- Ensure background workers and queues are paused or that you have monitoring in place to watch for job failures.

Staging (recommended first)
--------------------------
1. On the staging host or CI runner, switch to the release branch and pull the latest changes:

```bash
git fetch origin
git checkout chore/tenant-uuid-indexes
git pull origin chore/tenant-uuid-indexes
```

2. Install dependencies and build assets (if required):

```bash
composer install --no-dev --optimize-autoloader
npm ci --prefer-offline
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

3. Run migrations (this will add non-unique `tenant_uuid` indexes):

```bash
php artisan migrate --force
```

4. Verify backfill state and indexes:

```bash
php artisan tenant:verify-uuids
# optionally inspect indexes
# MySQL example:
mysql -u $DB_USER -p$DB_PASS -e "SHOW INDEX FROM clients WHERE Column_name='tenant_uuid'" $DB_NAME
```

5. Run smoke tests and watch logs for 30–60 minutes:
- Run the Cypress E2E suite (or the relevant subset) in staging.
- Tail web and worker logs for errors.

6. If everything looks good, proceed to production canary.

Production canary & full deploy
-------------------------------
1. TAKE A FULL DB BACKUP / SNAPSHOT
- MySQL (example):

```bash
mysqldump -u $DB_USER -p$DB_PASS --single-transaction --quick --lock-tables=false $DB_NAME > backup_$(date +%F_%T).sql
```

- PostgreSQL (example):

```bash
pg_dump -U $DB_USER -h $DB_HOST -Fc $DB_NAME -f backup_$(date +%F_%T).dump
```

2. Choose a small canary set (1–2 tenants) if you can restrict traffic, or run during a low-traffic maintenance window.

3. Put app into maintenance mode (optional but recommended for risk-averse deploys):

```bash
php artisan down --message="Applying tenant_uuid index migration"
```

4. Run migrations on production:

```bash
php artisan migrate --force
```

5. Verify counts and health:

```bash
php artisan tenant:verify-uuids
# Run smoke tests and monitor logs/alerts
```

6. Rollback steps (if needed):
- Run `php artisan migrate:rollback` to remove the last migration batch. Note: if multiple migrations were applied in the same batch, rollback will remove all of them.
- If you need to restore DB from backup:

MySQL:
```bash
mysql -u $DB_USER -p$DB_PASS $DB_NAME < backup_file.sql
```

Postgres:
```bash
pg_restore -U $DB_USER -h $DB_HOST -d $DB_NAME backup_file.dump
```

7. Bring app back up:

```bash
php artisan up
```

Monitoring suggestions
- Monitor web error rate, job/queue failures, and critical logs during the canary window.
- Verify that queries using `tenant_uuid` are using the new indexes (slow query log and EXPLAIN are useful).

Notes
- The migration we added is idempotent and defensive (wrapped in try/catch), so re-running is safe.
- The backfill was performed on the local snapshot and backups are in `tools/backfill-samples/`.
