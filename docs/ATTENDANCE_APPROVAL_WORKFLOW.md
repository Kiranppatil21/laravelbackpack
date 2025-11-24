# Bulk Attendance Approval Workflow

## Overview
Implements a four-step lifecycle for `employee_attendance_master` rows:

1. `draft` – Initial editable state (creation & updates allowed)
2. `submitted` – Pending review (no structural changes, can still view details)
3. `approved` – Reviewed; ready to lock (still not immutable)
4. `locked` – Finalized; edits & deletions blocked

An audit trail (`employee_attendance_audits`) records all create, update, delete and status transition actions with before/after JSON snapshots.

## Status Fields
- `status` (string, default `draft`)
- `approved_by` (user id, nullable)
- `approved_at` (timestamp, nullable)

## Transition Endpoints (Admin Routes)
```
POST /admin/bulk-attendance/{master}/submit   -> draft → submitted
POST /admin/bulk-attendance/{master}/approve  -> submitted → approved
POST /admin/bulk-attendance/{master}/lock     -> approved → locked
```
Each endpoint:
- Validates current status
- Authorizes by role (Super Admin / appropriate elevated roles)
- Writes an audit record with action: `submit` | `approve` | `lock`

## Edit Lock Enforcement
When `status = locked`:
- Controller `store()` rejects modifications with JSON error
- Delete (`destroy`) protected: cannot remove locked master
- UI hides or disables edit actions; badge shows `LOCKED`

## Audit Logging
Model: `EmployeeAttendanceAudit`
Fields:
- `attendance_master_id`
- `action` (create, update, delete, submit, approve, lock)
- `before` (JSON snapshot prior to change)
- `after` (JSON snapshot after change)
- `changed_by` (user id)
- Timestamps

## Running Smoke Test
Use the artisan command to validate workflow quickly inside a tenant context:
```
php artisan attendance:workflow-smoke {tenant_uuid?} --month=2025-11
```
Behavior:
- Initializes tenant (first tenant if none specified)
- Ensures Super Admin user & role
- Creates or reuses a site & master record (`draft`)
- Executes submit → approve → lock
- Attempts a post-lock update (expected rejection)
- Prints audit count and latest actions

## Re-running Migrations Safely
All tenant `Schema::create` migrations now guarded with `if (!Schema::hasTable(...))`.
Extended duplicate table migrations conditionally add missing columns when table already exists.

## Extensibility Notes
- Add additional statuses by defining constants on `EmployeeAttendanceMaster` and updating controller transition logic & UI buttons.
- For granular permissioning, layer Spatie permissions (e.g., `attendance.approve`) over role checks.
- Consider queueing audit writes if volume grows; current synchronous writes are simple & reliable.

## Next Steps
- Add automated Feature test covering transitions & audit assertions.
- Surface audit history in UI (filterable list under master record).
- Add per-row detail audit diff visualization.
