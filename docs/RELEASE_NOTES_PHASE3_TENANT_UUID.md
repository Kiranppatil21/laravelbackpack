# Release notes — Phase 3: tenant_uuid rollout

Date: 2025-10-30

Summary
-------
This release finalizes Phase 3 of the tenant UUID rollout. The main goals were to:

- Stabilize End-to-End tests (Cypress) by adding deterministic hydration markers and stable selectors.
- Add deterministic test helpers and fixtures so E2E specs (employee creation) are reliable.
- Backfill tenant UUIDs where missing and provide an idempotent artisan backfill command.
- Add non-unique indexes on `tenant_uuid` for performance and begin an automated, protected rollout (staging → production). 
- Add runbooks and GitHub Actions workflows to safely perform migrations on staging and production.

What changed (high level)
-------------------------
- Frontend
  - `resources/js/app.jsx` — deterministic hydration marker added
  - `resources/js/Pages/Auth/Register.jsx` and `resources/js/Pages/Employees/Create.jsx` — `data-cy` stable test selectors

- Tests / E2E
  - `cypress/e2e/employee_create.cy.js` — rewritten to use deterministic setup
  - `cypress/fixtures/*` — added KYC fixtures

- Tooling
  - `tools/create_test_employee.php` — deterministic DB helper used by Cypress
  - `tools/estimate_index_dryrun.sh` — index-estimate helper

- Backfill & Migrations
  - `app/Console/Commands/BackfillTenantUuids.php` — artisan `tenant:backfill-uuids` (dry-run & remediation)
  - `database/migrations/2025_10_30_110000_add_tenant_uuid_indexes.php` — non-unique `tenant_uuid` indexes (idempotent)

- CI / Deploy
  - `.github/workflows/staging-migration.yml` — manual staging migration workflow (requires `staging` secrets/environment)
  - `.github/workflows/production-migration.yml` — manual production migration workflow (protected environment)

- Documentation
  - `docs/PHASE3_DEPLOY_RUNBOOK.md`, `docs/PRODUCTION_CANARY_RUNBOOK.md`, `docs/PR_PHASE3_TENANT_UUID_INDEXES.md`, `docs/SLACK_TEMPLATES.md`

Verification & artifacts
------------------------
The following verification and artifacts were produced during rollout. Replace the placeholders below with the real URLs/paths before merging the PR.

- Staging workflow run (migration + smoke tests): https://github.com/Kiranppatil21/laravelbackpack/actions/runs/18939307103
- PR and release notes: https://github.com/Kiranppatil21/laravelbackpack/pull/7
- Production workflow run (canary): {{PRODUCTION_RUN_URL}}
- Cypress artifacts (screenshots/videos) attached to the staging run: {{CYPRESS_ARTIFACTS_URL}}
- Production DB backup location and checksum: {{BACKUP_LOCATION}} (checksum: {{BACKUP_CHECKSUM}})

Key verification commands (run on host or via Actions logs)
--------------------------------------------------------
- php artisan migrate --force
- php artisan tenant:verify-uuids
- npm ci && npm run build (if assets required on server)

Rollback plan
-------------
1. If migration causes errors, stop and investigate logs in Actions / server.
2. If data corruption or index problems occur, restore DB from the saved backup at {{BACKUP_LOCATION}}.
3. If necessary, revert the PR that created the index migration and redeploy.

Next steps / followups
----------------------
- Monitor application metrics and error rates for 24–72 hours.
- After a stable observation window, prepare a separate RFC/PR to remove or deprecate legacy `tenant_id` columns (if desired).

Notes for PR reviewers
---------------------
- Please replace the `{{...}}` placeholders with the actual run URLs and backup info before merging.
- Ensure the `staging` and `production` GitHub Environments contain the SSH secrets referenced in the workflows.

Contact
-------
For questions or to request additional artifacts, ping @your-oncall or the team in the release Slack channel.
