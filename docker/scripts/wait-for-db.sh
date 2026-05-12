#!/bin/sh
set -euo

echo "Waiting for database..."

until php bin/console dbal:run-sql "SELECT 1"; do
  sleep 2
done

echo "Database ready"