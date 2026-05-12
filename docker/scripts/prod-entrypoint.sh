#!/bin/sh
set -eu

/scripts/wait-for-db.sh

# Normally we'd never run migrations in a production environment,
# but this one is only for local testing/mimicking purposes

echo "Running migrations..."
php bin/console doctrine:migrations:migrate \
  --env=prod \
  --no-interaction

exec apache2-foreground