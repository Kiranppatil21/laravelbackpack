# Slack templates for Phase 3 canary & rollout

Use the following short messages to notify stakeholders before/during/after the production canary.

Pre-Canary (announce maintenance window)
-----------------------------------------
:warning: Heads up — tenant_uuid index rollout (Phase 3)

We will apply non-unique `tenant_uuid` indexes to tenant-scoped tables in production.

- Window: <YYYY-MM-DD HH:MM UTC>
- Duration: ~30-60 minutes
- Impact: Short maintenance window; writes may be paused during migration
- Backup: Full DB backup completed: <backup-path-or-id>

Please standby for status updates. Ping @oncall and @product for approvals.

During-Canary (start)
---------------------
:rocket: Starting production canary for tenant_uuid index rollout

- Run started by: @<operator>
- Run URL: <actions-run-url>
- Snapshot/Backup: <backup-path-or-id>

I will post progress updates and smoke test results. If you see regressions, respond with `rollback`.

During-Canary (progress)
-------------------------
:mag: Progress update: migration step completed: <step details>

- tenant:verify-uuids output: <paste output>
- Cypress smoke tests: <passed/failed + link to artifacts>
- Monitoring: <error rate, queue failures, slow queries>

If anything critical appears, say `rollback` and I'll initiate rollback steps.

Post-Canary (success)
----------------------
:white_check_mark: Production canary succeeded

- Migration applied: non-unique tenant_uuid indexes added
- Verification: `php artisan tenant:verify-uuids` -> 0 missing rows
- Smoke tests: passed
- Monitoring window: no issues observed

Phase 3 is complete. Please close relevant tickets and update release notes.

Post-Canary (failure)
----------------------
:x: Production canary encountered an issue — initiating rollback

- Issue: <summary>
- Action: running `php artisan migrate:rollback` (or restoring DB from backup if required)
- Rollback status: <updates>

Follow-ups: create a post-mortem and plan a re-run after mitigation.
