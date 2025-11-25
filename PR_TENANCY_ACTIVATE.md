Title: feat(tenancy): add admin 'Activate' tenant button + docs

Summary:
This small PR adds a per-row "Activate" button in the Backpack Tenants list so super-admins can mark tenants as active, plus `docs/tenancy.md` with local development instructions for subdomain-based tenancy (Valet and /etc/hosts guidance).

Why:
- Allows super-admins to approve/activate tenant accounts from the admin UI.
- Provides clear developer instructions for running the app with subdomain tenancy locally.

What changed (high level):
- resources/views/vendor/backpack/crud/buttons/tenant_activate_button.blade.php (new)
- app/Http/Controllers/Admin/TenantCrudController.php (activate action added)
- tests/Feature/AdminActivateTenantTest.php (feature test added)
- docs/tenancy.md (new documentation)
- app/Models/Concerns/BelongsToTenant.php (added earlier; included in this branch)
- Several models updated to use `BelongsToTenant` concern (for tenant scoping)

Testing:
- PHPUnit full suite: 49 passing tests locally (136 assertions)
- New feature test `AdminActivateTenantTest` passes.

Notes & follow-up:
- This PR is intentionally small and non-breaking. A separate follow-up PR will introduce a larger stancl/tenancy model alignment refactor (normalize `tenants` table to stancl expectations, switch to UUID/string ids and `data` JSON column, and adjust tenant creation flows). That larger refactor is staged separately to keep review small and safe.

How to open the PR locally:
1. Create and checkout a topic branch (example):
   git checkout -b feat/tenancy-activate-docs
2. Commit your changes and push:
   git add . && git commit -m "feat(tenancy): add admin activate button + docs" && git push -u origin feat/tenancy-activate-docs
3. Open a PR with GitHub CLI (if `gh` is installed & authenticated):
   gh pr create --fill --base main --head feat/tenancy-activate-docs

If you'd like, I can create and open this PR for you (requires GitHub authentication from your environment).