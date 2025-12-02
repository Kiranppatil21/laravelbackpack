#!/usr/bin/env bash
set -euo pipefail

# Helper to run the RBAC PHPUnit test against a disposable MySQL container.
# Usage:
#   ./scripts/run-rbac-test.sh
# Environment variables (optional):
#   MYSQL_ROOT_PASSWORD, MYSQL_DATABASE, MYSQL_USER, MYSQL_PASSWORD, MYSQL_PORT, CONTAINER_NAME

MYSQL_ROOT_PASSWORD=${MYSQL_ROOT_PASSWORD:-secret}
MYSQL_DATABASE=${MYSQL_DATABASE:-lb_test}
MYSQL_USER=${MYSQL_USER:-lb}
MYSQL_PASSWORD=${MYSQL_PASSWORD:-secret}
MYSQL_PORT=${MYSQL_PORT:-3307}
CONTAINER_NAME=${CONTAINER_NAME:-lb-test-mysql}

function finish {
  echo "Cleaning up container ${CONTAINER_NAME}..."
  if command -v docker >/dev/null 2>&1; then
    docker rm -f "$CONTAINER_NAME" >/dev/null 2>&1 || true
  fi
}
trap finish EXIT

if ! command -v docker >/dev/null 2>&1; then
  echo "Error: docker is not installed or not in PATH. Please install Docker to run this script." >&2
  exit 2
fi

echo "Starting MySQL container ${CONTAINER_NAME} (port ${MYSQL_PORT})..."
docker run -d --name "$CONTAINER_NAME" \
  -e MYSQL_ROOT_PASSWORD="$MYSQL_ROOT_PASSWORD" \
  -e MYSQL_DATABASE="$MYSQL_DATABASE" \
  -e MYSQL_USER="$MYSQL_USER" \
  -e MYSQL_PASSWORD="$MYSQL_PASSWORD" \
  -p "$MYSQL_PORT":3306 \
  mysql:8.0 >/dev/null

echo "Waiting for MySQL to accept connections..."
for i in {1..60}; do
  if docker exec "$CONTAINER_NAME" mysqladmin ping -h localhost -uroot -p"$MYSQL_ROOT_PASSWORD" --silent >/dev/null 2>&1; then
    echo "MySQL ready" && break
  fi
  echo "  still waiting... ($i)"; sleep 2
done

echo "Exporting test DB env vars for the test run"
export DB_CONNECTION=mysql
export DB_HOST=127.0.0.1
export DB_PORT="$MYSQL_PORT"
export DB_DATABASE="$MYSQL_DATABASE"
export DB_USERNAME="$MYSQL_USER"
export DB_PASSWORD="$MYSQL_PASSWORD"

echo "Preparing application and running migrations (testing)..."
php artisan config:clear || true
php artisan key:generate --ansi || true
composer dump-autoload -o || true

if ! php artisan migrate --env=testing --no-interaction --force; then
  echo "Migration failed. Dumping processlist and InnoDB status for debugging:" >&2
  docker exec "$CONTAINER_NAME" mysql -uroot -p"$MYSQL_ROOT_PASSWORD" -e "SHOW FULL PROCESSLIST; SHOW ENGINE INNODB STATUS;"
  exit 3
fi

echo "Running RBAC PHPUnit test..."
set +e
./vendor/bin/phpunit --colors=always tests/Feature/RoleBasedAuthorizationTest.php
TEST_STATUS=$?
set -e

echo "Test run finished (exit $TEST_STATUS). Listing any artifacts in tests/_output:"
ls -lah tests/_output || true

exit $TEST_STATUS
