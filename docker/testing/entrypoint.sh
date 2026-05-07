#!/bin/sh
set -e

echo "Waiting for database..."
until php bin/console doctrine:query:sql "SELECT 1" >/dev/null 2>&1; do
  sleep 2
done

echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --env=prod

exec apache2-foreground