# Running Cypress locally & CI parallelization

This project includes Cypress E2E specs for the employee onboarding + KYC flow under `cypress/e2e`. The repository also contains a GitHub Actions job (`.github/workflows/cypress.yml`) that runs Cypress in CI and supports optional parallelization via the Cypress Dashboard.

Quick checklist (macOS, zsh):

- Install node deps and Cypress if you haven't already:

  npm install

- Build assets (or run the dev server in another terminal):

  npm run build    # for a production build the CI uses
  # or for local development
  npm run dev

- Prepare the database and run migrations:

  cp .env.example .env
  composer install
  php artisan key:generate
  # configure DB settings in .env and then:
  php artisan migrate --seed

- Start the Laravel dev server (this is what Cypress verifies by default):

  php artisan serve --port=8000

- Run Cypress (open interactive runner or run headless):

  # interactive (recommended while iterating locally)
  npx cypress open

  # headless (CI-like)
  npx cypress run --spec "cypress/e2e/**"

Troubleshooting tips
- If Cypress fails with "Could not verify that your server is running", confirm the baseUrl used in `cypress.config.js` (defaults to `http://127.0.0.1:8000`) and that `php artisan serve` is reachable from the machine running Cypress. If you run the backend on a different port or host, update the `baseUrl` in `cypress.config.js` or set the `CYPRESS_BASE_URL` env var when running tests.
- The project uses `type: "module"` in `package.json`, so the Cypress config is ESM — if you edit `cypress.config.js` keep the `export default` form.
- If you see file upload failures in tests, ensure the fixture files live under `cypress/fixtures` and that the test user has permission to create any temporary upload directories.

CI parallelization / Dashboard
- To enable parallelization and Cypress Dashboard recording in CI you can add the repo secret `CYPRESS_RECORD_KEY` (the Cypress record key) in GitHub: Settings → Secrets → Actions. When that secret exists the CI workflow will run `npx cypress run --record --parallel` and upload video/artifacts to the Dashboard.
- If you don't want to use the Dashboard, the workflow will still run tests headlessly without `--record`.

If you'd like, I can add a small Makefile or npm scripts to wrap these commands for convenience.
