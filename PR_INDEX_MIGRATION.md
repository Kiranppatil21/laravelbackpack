# Index migration PR template

This PR adds non-unique indexes on `tenant_uuid` for tenant-scoped tables. These indexes must be applied after the tenant_uuid backfill has completed and been verified.

Why separate PR?
- Index creation on large tables can be expensive; we keep it separate so it can be scheduled and monitored.

Migration included:
- `database/migrations/2025_10_29_200000_add_tenant_uuid_indexes.php`

How to validate before merging
1. Ensure backfill is complete and verified using `php artisan tenant:verify-uuids`.
2. On a production snapshot, run the migration and monitor replica lag and long-running queries.
3. Confirm index presence:

```sql
SHOW INDEX FROM clients WHERE Key_name = 'idx_clients_tenant_uuid';
```

Rollback notes
- The migration's `down()` attempts to drop indexes by the names used when created. Depending on your DB and naming conventions, the drop may need manual handling.

Monitoring recommendations
- Watch replica lag, table-level locks, and slow queries while indexes are being created.
- If the index build is long, prefer online index creation options (Percona/pt-online-schema-change for MySQL or CREATE INDEX CONCURRENTLY for Postgres).

Checklist
- [ ] Backfill verified (counts of missing tenant_uuid = 0 or acceptable threshold)
- [ ] Migration run on snapshot and verified
- [ ] Monitor alerts configured
- [ ] Merge PR and apply migration during the maintenance window

