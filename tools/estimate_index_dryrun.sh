#!/usr/bin/env bash
# estimate_index_dryrun.sh
# Small helper to gather table stats and give a rough estimate for index creation
# Use this against a read-only production snapshot or an environment copy.

set -euo pipefail

usage() {
  cat <<EOF
Usage: $0 -t TABLE -d DB_NAME -u DB_USER [-h DB_HOST] [-P DB_PORT] [-m mysql|pg] [-r ROWS_PER_SEC]

Example:
  $0 -t clients -d mydb -u dbuser -h localhost -m mysql -r 10000

This script prints row counts, data_length, index_length (MySQL) and a heuristic estimate
for how long an index creation might take based on ROWS_PER_SEC (default 10000 rows/sec).
EOF
}

TABLE=""
DB_NAME=""
DB_USER=""
DB_HOST="localhost"
DB_PORT="3306"
DB_TYPE="mysql"
ROWS_PER_SEC=10000

while getopts "t:d:u:h:P:m:r:?" opt; do
  case $opt in
    t) TABLE=$OPTARG ;; d) DB_NAME=$OPTARG ;; u) DB_USER=$OPTARG ;; h) DB_HOST=$OPTARG ;; P) DB_PORT=$OPTARG ;; m) DB_TYPE=$OPTARG ;; r) ROWS_PER_SEC=$OPTARG ;; ?|*) usage; exit 1 ;;
  esac
done

if [ -z "$TABLE" ] || [ -z "$DB_NAME" ] || [ -z "$DB_USER" ]; then
  usage
  exit 1
fi

echo "Gathering stats for $TABLE on $DB_TYPE://$DB_HOST:$DB_PORT/$DB_NAME"

if [ "$DB_TYPE" = "mysql" ]; then
  # MySQL queries
  echo "-- ROW COUNT --"
  mysql -u "$DB_USER" -h "$DB_HOST" -P "$DB_PORT" -D "$DB_NAME" -se "SELECT COUNT(*) FROM \\\`$TABLE\\\`;" || true

  echo "-- TABLE SIZE (information_schema) --"
  mysql -u "$DB_USER" -h "$DB_HOST" -P "$DB_PORT" -D information_schema -se \
    "SELECT TABLE_ROWS, DATA_LENGTH, INDEX_LENGTH, ROUND((DATA_LENGTH+INDEX_LENGTH)/1024/1024,2) as MB FROM tables WHERE table_schema='$DB_NAME' AND table_name='$TABLE';" || true

  ROWS=$(mysql -u "$DB_USER" -h "$DB_HOST" -P "$DB_PORT" -D "$DB_NAME" -se "SELECT COUNT(*) FROM \\\`$TABLE\\\`;" | tr -d '\n' || echo 0)

elif [ "$DB_TYPE" = "pg" ] || [ "$DB_TYPE" = "postgres" ]; then
  echo "-- ROW COUNT --"
  PGPASSWORD=${PGPASSWORD:-} psql -U "$DB_USER" -h "$DB_HOST" -p "$DB_PORT" -d "$DB_NAME" -qt -c "SELECT COUNT(*) FROM $TABLE;" || true

  echo "-- TABLE SIZE --"
  PGPASSWORD=${PGPASSWORD:-} psql -U "$DB_USER" -h "$DB_HOST" -p "$DB_PORT" -d "$DB_NAME" -qt -c "SELECT pg_total_relation_size('$TABLE') as bytes;" || true

  ROWS=$(PGPASSWORD=${PGPASSWORD:-} psql -U "$DB_USER" -h "$DB_HOST" -p "$DB_PORT" -d "$DB_NAME" -qt -c "SELECT COUNT(*) FROM $TABLE;" | tr -d '\n' || echo 0)
else
  echo "Unsupported DB type: $DB_TYPE" >&2
  exit 2
fi

if [[ "$ROWS" =~ ^[0-9]+$ ]]; then
  echo "Estimated rows: $ROWS"
  EST_SEC=$(( ROWS / ROWS_PER_SEC ))
  EST_MIN=$(( EST_SEC / 60 ))
  echo "Heuristic estimate (rows_per_sec=$ROWS_PER_SEC): ~${EST_SEC}s (~${EST_MIN}m)"
  echo "Note: This is a rough heuristic. Measure on a snapshot for more accurate timing."
else
  echo "Could not determine row count. Output: $ROWS"
fi

echo "Recommendation: run the script on a production snapshot and/or create the index on a copy of the table to measure true timing. Consider using pt-online-schema-change for large tables."
