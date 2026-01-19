PR Title
========
Phase 2 — Sanctum API auth, token SPA flows, demo seeding, and billing/webhook hardening

PR Description
---------------
This PR implements Phase 2 of the SaaS conversion and hardens billing/webhook workflows.

Highlights
- Laravel Sanctum API endpoints for token-based auth (login/register/logout/user).
- Inertia/React token login/register/dashboard pages and flows.
- Server-side role enforcement for /api/dashboard with middleware fallback.
- Demo users seeder and tests to exercise demo login flows.
- Billing & webhooks: Razorpay + Stripe webhook handlers, background jobs to process payments, and admin retry UI.
- Centralized `RazorpayResolver` service and `RazorpayResolverInterface` for consistent SDK resolution and easier testing.
- `ProcessRazorpayPayment` accepts an optional API instance and resolves the resolver from the container; controllers are constructor-injected with the resolver.

Files changed (high level)
- `routes/api.php`, `routes/web.php` — API and webhook routes
- `app/Http/Controllers/Api/*` — auth controllers
- `app/Http/Controllers/RazorpayController.php`, `app/Http/Controllers/SignupController.php`, `app/Http/Controllers/Admin/RazorpayPaymentCrudController.php`
- `app/Jobs/ProcessRazorpayPayment.php`
- `app/Services/RazorpayResolver.php`, `app/Services/RazorpayResolverInterface.php`
- `app/Providers/AppServiceProvider.php` — resolver bound to container
- `tests/Unit/RazorpayResolverTest.php`, `tests/Feature/ProcessRazorpayPaymentIntegrationTest.php`, `tests/Feature/SignupProvisioningE2ETest.php`
- `PR_BODY.md`, `PR_READY.md` (this file)

Checklist for reviewers / CI
- [ ] Run full test suite in CI (GitHub Actions) — all tests must pass.
- [ ] Verify environment variables for production/staging: RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET, RAZORPAY_WEBHOOK_SECRET, STRIPE keys.
- [ ] Confirm queue workers are configured in staging/production.
- [ ] Sanity-check public signup and webhook endpoints in staging (use test keys and test webhooks).
- [ ] Review resolver binding in `AppServiceProvider` and suggest moving to a dedicated provider if preferred.

Suggested reviewers
- @Kiranppatil21 (repo owner)
- @<backend-reviewer> (backend lead / reviewer)
- @<frontend-reviewer> (Inertia/React reviewer)
- @<devops-reviewer> (CI/deployment reviewer)

How to open the PR locally

If you have the GitHub CLI (`gh`) installed and authenticated, run:

```bash
git checkout -b phase2
git add -A
git commit -m "Phase 2: Sanctum API auth, token SPA flows, demo seeding, billing/webhook hardening"
git push --set-upstream origin phase2
gh pr create --fill --title "Phase 2 — Sanctum API auth, token SPA flows, demo seeding, and billing/webhook hardening" --body-file PR_BODY.md --base main
```

If you prefer to create the PR via the GitHub API (requires a personal access token with repo scope), run:

```bash
GITHUB_TOKEN=ghp_xxx_here
curl -H "Authorization: token $GITHUB_TOKEN" \
     -X POST \
     -d '{"title":"Phase 2 — Sanctum API auth, token SPA flows, demo seeding, and billing/webhook hardening","head":"phase2","base":"main","body":"'"$(sed -e 's/"/\\"/g' PR_BODY.md)"'"}' \
     https://api.github.com/repos/Kiranppatil21/laravelbackpack/pulls
```

Manual PR creation
- You can also open a PR via the GitHub web UI: go to the repository page, switch to the `phase2` branch and choose "Compare & pull request".

Notes
- The `PR_BODY.md` file in the repo is a ready-to-paste long-form description (use that for the PR body if you want more detail).
- CI should be allowed to run the full test suite on this branch; the tests pass locally (48 passed, 0 failed at the time of PR draft).
