# Contributing

Thanks for contributing! This document describes the branching and PR policy used by this repository.

## Branching strategy

- `main` is the protected production branch. All changes to `main` must be via Pull Request and pass CI.
- Feature and fix branches should be named using one of these prefixes:
  - `feat/` for new features (example: `feat/signup-react`)
  - `fix/` for bug fixes (example: `fix/razorpay-signature`) 
  - `chore/` for maintenance or tooling changes (example: `chore/deps`) 
  - `docs/` for documentation updates

## Pull requests

- Open a PR from your feature branch into `main`.
- Use a clear title and a short description. Include testing steps and any manual QA notes.
- Requests should include at least one reviewer and at least one approving review before merge.
- Ensure CI (GitHub Actions) passes before merging.

## Commit messages

- Use short, imperative commit messages, e.g., `fix: handle null receipt in job`.
- For large changes, squash or structure commits logically so the PR is easy to review.

## Local development checklist

1. Pull latest `main` and create a feature branch: `git checkout -b feat/your-feature`
2. Run tests locally: `composer install && php artisan test`
3. Lint/format if appropriate (we use `pint` in dev deps): `./vendor/bin/pint`.
4. Open a PR when ready.

## Code review

- Reviewers should verify: readability, tests, security implications (especially for auth/billing), and CI green.
- Ask for changes when necessary; merge when at least one approval and CI passes.

If you have questions about this process, open an issue or ping a repo maintainer.
