#!/bin/sh
set -eu

echo "Waiting for database..."

i=0
max="${DB_WAIT_MAX_TRIES:-30}"

until php bin/console dbal:run-sql "SELECT 1" >/dev/null 2>&1; do
  i=$((i + 1))

  if [ "$i" -ge "$max" ]; then
    echo "Database not ready after $max attempts"
    exit 1
  fi

  sleep 2
done

echo "Database ready"