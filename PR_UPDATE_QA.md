## QA summary to paste into draft PR

This PR includes the tenancy `tenant_uuid` rollout scaffolding and Phase 3 Agency/Client features. Final QA steps performed and artifacts added in this branch:

- Tests
  - Full PHPUnit suite executed locally: all tests passed (54 tests, 145 assertions).
  - Added `tests/Feature/ClientApiTest.php` (tenant create, unauthorized, foreign-agency handling).

- Static analysis
  - Ran PHPStan with `vendor/bin/phpstan analyse -c phpstan.neon app` and resolved all reported issues.
  - Small fixes applied to two Backpack admin controllers to satisfy static analysis without changing runtime behavior (added `AuthorizesRequests` trait, `@method` annotations, and small null-safety rewrites).

- Runbook & tooling
  - `docs/staged-pk-swap-draft.md` expanded with verification SQL, backfill guidance, index addition guidance, rollback steps, and monitoring suggestions.
  - Added `app/Console/Commands/tenant:verify-uuids` — verification helper to quickly count rows missing `tenant_uuid` and spot-check samples.
  - Added `app/Console/Commands/tenant:backfill-uuids` — idempotent chunked backfill with `--dry-run` and `--chunk` options for safe canaries.

- What I pushed
  - Branch: `feat/tenancy-pk-swap-draft` (pushed with the above changes and test additions).

- Recommended next steps (safe rollout)
  1. Run verification on a production snapshot: `php artisan tenant:verify-uuids`.
  2. On a snapshot, run dry-run backfill for a small chunk: `php artisan tenant:backfill-uuids --dry-run --chunk=100`.
  3. Run a small real backfill on the snapshot and re-run verification.
  4. If validated, run a canary backfill on a small subset of production (e.g., single tenant or single table) and monitor.
  5. After successful backfill, add the non-unique index migrations in a separate PR and deploy.

- Decision guidance
  - Keep this PR as DRAFT until canary backfill and verification are complete; merging the code-only changes is low-risk if you do NOT run the backfill yet.

If you'd like, I can update the PR body with this content automatically (requires GitHub CLI configured in this environment). Otherwise, paste this into the draft PR body and mark any checklist items as done.
