# Production Canary Runbook — Phase 3 tenant_uuid index rollout

This document describes the exact steps, checks, and runnable commands to perform a production canary for the Phase 3 `tenant_uuid` index rollout. It assumes you have already backfilled `tenant_uuid` values and verified them locally/staging.

Prerequisites
- Full, verified production DB backup or cloud snapshot. Record backup file path and checksum before proceeding.
- Maintenance window scheduled and stakeholders notified.
- SSH access to production host with a deploy key and the `deploy` user able to run `composer`, `php artisan`, and (optionally) `npm`.
- A plan for online index creation (pt-online-schema-change) if tables are large.

High-level plan
1. Take/verify production DB backup.
2. Deploy the code (checkout `main`) to production host.
3. Put app into maintenance mode.
4. Run migrations (`php artisan migrate --force`). This adds the non-unique `tenant_uuid` indexes from the migration file.
5. Run `php artisan tenant:verify-uuids` and confirm 0 missing rows.
6. Run smoke tests (Cypress subset) and monitor for 30–60 minutes.
7. If green, mark Phase 3 complete and remove maintenance mode.
8. If problems, rollback migration/migrate:rollback or restore DB from backup.

Backup commands (MySQL)
```bash
BACKUP_DIR=/backups/$(date +%F_%H%M%S)
mkdir -p "$BACKUP_DIR"
mysqldump -u "$DB_USER" -p"$DB_PASS" --single-transaction --quick --skip-lock-tables --databases "$DB_NAME" > "$BACKUP_DIR"/backup.sql
gzip "$BACKUP_DIR"/backup.sql
sha256sum "$BACKUP_DIR"/backup.sql.gz > "$BACKUP_DIR"/backup.checksum
```

Backup commands (Postgres)
```bash
BACKUP_DIR=/backups/$(date +%F_%H%M%S)
pg_dump -U "$DB_USER" -h "$DB_HOST" -Fc "$DB_NAME" -f "$BACKUP_DIR"/backup.dump
sha256sum "$BACKUP_DIR"/backup.dump > "$BACKUP_DIR"/backup.checksum
```

Production canary (step-by-step)

1) Put app into maintenance mode
```bash
ssh -i ~/.ssh/prod_deploy deploy@prod.example.com -p 22 'cd /var/www/laravelbackpack && php artisan down --message="Applying tenant_uuid index migration"'
```

2) Deploy code and install dependencies
```bash
ssh -i ~/.ssh/prod_deploy deploy@prod.example.com -p 22 <<'SSH'
cd /var/www/laravelbackpack
git fetch origin
git checkout main
git reset --hard origin/main
composer install --no-dev --optimize-autoloader
if [ -f package.json ]; then
  npm ci --prefer-offline
  npm run build || true
fi
SSH
```

3) Run migrations and verification
```bash
ssh -i ~/.ssh/prod_deploy deploy@prod.example.com -p 22 <<'SSH'
cd /var/www/laravelbackpack
php artisan migrate --force
php artisan tenant:verify-uuids
SSH
```

4) Run smoke tests (from CI/runner pointing at production URL)
```bash
export CYPRESS_BASE_URL="https://app.example.com"
npx cypress run --spec "cypress/e2e/employee_create.cy.js"
```

5) Bring system back up
```bash
ssh -i ~/.ssh/prod_deploy deploy@prod.example.com -p 22 'cd /var/www/laravelbackpack && php artisan up'
```

Monitoring checklist (first 60 minutes)
- Check application error rate (Sentry/NewRelic) for spikes.
- Watch queue failures and worker logs.
- Confirm tenant queries use new index (EXPLAIN) and no long-running queries in slow log.
- Confirm critical E2E flows succeed (login, create employee, KYC upload).

Rollback steps
- If migration introduces problems, first try `php artisan migrate:rollback` for the last batch:
```bash
ssh -i ~/.ssh/prod_deploy deploy@prod.example.com -p 22 'cd /var/www/laravelbackpack && php artisan migrate:rollback'
```
- If rollback is insufficient or unsafe, restore DB from backup (see restore commands in this doc).

Dry-run guidance
- Use the `tools/estimate_index_dryrun.sh` tool against a read-only production snapshot to estimate table sizes and index creation cost (see scripts in repo).
- Prefer `pt-online-schema-change` for very large tables; test the tool on a snapshot first.

Finalize
- If monitoring remains green after canary window, record the time, who approved, and close Phase 3.
