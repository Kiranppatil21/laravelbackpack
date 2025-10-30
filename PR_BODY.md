Title: Phase 2 — Sanctum API auth, token SPA flows, demo seeding, and billing/webhook hardening

Summary
-------
This branch implements Phase 2 of the SaaS conversion:

- Laravel Sanctum API endpoints for token-based auth (login/register/logout/user).
- Inertia/React token login/register/dashboard pages and flows.
- Server-side role enforcement for /api/dashboard with middleware fallback.
- Demo users seeder and tests to exercise demo login flows.
- Billing & webhooks: Razorpay + Stripe webhook handlers, background jobs to process payments, and an admin retry UI.
- Robustness improvements:
  - Centralized `RazorpayResolver` service that prefers container-bound SDK fakes and falls back to env-based instantiation.
  - `ProcessRazorpayPayment` job refactored to accept an optional API instance and to resolve the resolver from the container.
  - Controllers updated to constructor-inject `RazorpayResolver` for consistent DI and testability.

Why
---
This PR completes Phase 2: developer-facing API auth + SPA token flows, role-protected API dashboard, and hardened billing/webhook flows that are testable and robust in environments without queue workers.

Files/areas changed (high level)
--------------------------------
- API routes and controllers for auth (login/register/user/logout)
- `app/Services/RazorpayResolver.php` — new
- Job refactor: `app/Jobs/ProcessRazorpayPayment.php`
- Controllers: `RazorpayController`, `Admin\RazorpayPaymentCrudController`, `SignupController` updated to inject the resolver
- `tests/Unit/RazorpayResolverTest.php` and `tests/Feature/ProcessRazorpayPaymentIntegrationTest.php` added
- DemoUsersSeeder + related tests

Checklist for reviewers / CI
---------------------------
- [ ] Run full test suite in CI (GitHub Actions) — all tests must pass.
- [ ] Verify environment variables for production/staging: RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET, RAZORPAY_WEBHOOK_SECRET, STRIPE keys.
- [ ] Confirm queue workers are configured in staging/production (jobs are idempotent; inline fallback remains for environments without workers).
- [ ] Sanity-check public signup and webhook endpoints in staging (use test keys and test webhooks).
- [ ] Optionally review the resolver binding in `AppServiceProvider` and suggest moving to a dedicated provider if preferred.

How to run locally
-------------------
1. Install dev deps and migrate/test DB:

   php artisan migrate --env=testing
   php artisan test

2. To run the new end-to-end provisioning test only:

   php artisan test --filter SignupProvisioningE2ETest

Notes
-----
This PR purposely avoids editing vendor code. Tests stub SDKs by binding `Razorpay\Api\Api` into the container. The `RazorpayResolver` prefers container bindings so tests can inject fakes.

If you want, I can open the PR on GitHub for you (I can prepare the GH UI text). Automatic opening requires the GitHub CLI or a token.

Local test status: All tests passed locally at the time of this PR draft (48 passed, 0 failed).
