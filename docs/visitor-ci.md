# Visitor CI & local testing

This document explains how the visitor E2E CI job works and provides copy/paste commands to test locally.

## What the CI workflow does

The workflow `.github/workflows/visitor_e2e.yml` performs a full end-to-end smoke test:

1. Boots PHP & Node, installs deps and builds assets.
2. Runs migrations and seeds (`TestRolesSeeder`, `VisitorSeeder`).
3. Starts a database queue worker and the Laravel dev server.
4. Creates a CI host user and posts a signed check-in (uses either API key or HMAC secret).
5. Waits for the notification to be processed and asserts mail delivery by scanning `storage/logs/laravel.log` (CI uses `MAIL_MAILER=log`).

## Required repository secrets (add in GitHub Settings → Secrets → Actions)

- `VISITOR_API_KEY` — optional simple API key accepted by the `VerifyVisitorApiKey` middleware, OR
- `VISITOR_HMAC_SECRET` — shared HMAC secret used to sign kiosk/IoT POSTs.

At least one of the above must be present for the CI job to run.

## Local testing

### 1) Run app locally

```bash
cp .env.example .env
php artisan key:generate
php -r "file_exists('database/database.sqlite') || mkdir('database') && touch('database/database.sqlite');"
php artisan migrate
php artisan db:seed --class=TestRolesSeeder
php artisan db:seed --class=VisitorSeeder
php artisan queue:work --sleep=1 --tries=1 &
php artisan serve --port=8000
```

### 2) Sign and POST a check-in (HMAC)

Use the provided scripts in `scripts/` or the curl example below.

Node example (already included):

```bash
VISITOR_HMAC_SECRET=your_secret_here node scripts/sign-node.js
```

Python example (already included):

```bash
VISITOR_HMAC_SECRET=your_secret_here python3 scripts/sign-python.py
```

Manual curl example (HMAC):

```bash
PAYLOAD='{"name":"Local Device","email":"local@example.test","host_id":1}'
TS=$(date +%s)
SIG=$(printf "%s|%s" "$TS" "$PAYLOAD" | openssl dgst -sha256 -hmac "${VISITOR_HMAC_SECRET}" | awk '{print $NF}')
curl -v -X POST -H "Content-Type: application/json" -H "X-VISITOR-TIMESTAMP: $TS" -H "X-VISITOR-SIGNATURE: $SIG" -d "$PAYLOAD" http://127.0.0.1:8000/api/visitors/checkin
```

Manual curl example (API key):

```bash
VISITOR_API_KEY=myapikey
PAYLOAD='{"name":"Local Device","email":"local@example.test","host_id":1}'
curl -v -X POST -H "Content-Type: application/json" -H "X-VISITOR-API-KEY: $VISITOR_API_KEY" -d "$PAYLOAD" http://127.0.0.1:8000/api/visitors/checkin
```

### 3) Verify mail/log

Because CI uses `MAIL_MAILER=log`, Laravel will append the email to `storage/logs/laravel.log`. After posting a check-in you can inspect the log:

```bash
tail -n 200 storage/logs/laravel.log
```

You should see an entry containing the host email (for seeded host user: `ci_host@example.test`) and the notification content.

## Re-run CI job after adding secret

After adding a secret in the GitHub repo settings, re-run the workflow by either:

- Pushing an empty commit on this branch:

```bash
git commit --allow-empty -m "ci: trigger visitor E2E" && git push
```

- Or from the PR page on GitHub, open the Actions tab and re-run the failed job.


## Troubleshooting

- If the CI job times out waiting for the log entry, download uploaded artifacts (server.log, /tmp/queue.log, storage/logs/laravel.log) from the workflow run to debug queue and mail issues.
- If you prefer MailHog instead of `log` driver, tell me and I can switch the workflow to start MailHog and poll its API.
