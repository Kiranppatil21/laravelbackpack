Title: chore(tenancy): add uuid and data columns to tenants table (backfill)

Summary:
This PR begins the stancl/tenancy model alignment by adding a `uuid` (string) and `data` (json) column to the central `tenants` table. Existing tenant rows are backfilled with generated UUIDv4 values.

Why this incremental PR:
- Avoids a destructive primary-key change in one step.
- Lets us update the application to reference `tenants.uuid` (and `data`) gradually.
- Keeps integer `id` in place so foreign keys remain valid while we update code/tests.

What changed:
- database/migrations/2025_10_29_100000_add_uuid_and_data_to_tenants_table.php (new)

Testing:
- Ran full PHPUnit suite locally (49 tests passed, 136 assertions) with the new migration applied in the test environment.

Next steps (follow-up PRs):
- Update stancl/tenancy config or Tenant model mapping to use `uuid` as the tenant identifier.
- Refactor tenant creation flows (`SignupController`, `CreateTenant` command) to create stancl Tenant model entries with UUID ids and populate `data`.
- Update tests and any FK relationships that assume integer tenant ids.
- Final migration to convert primary key to UUID (only after codebase fully supports it).

Rollback:
- The migration's `down()` drops the added `data` and `uuid` columns.

Note:
- This change is intentionally conservative; it starts the migration path without touching the primary key or FKs.
