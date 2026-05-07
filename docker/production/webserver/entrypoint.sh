#!/bin/sh
set -e

echo "Waiting for database through Doctrine..."
until php bin/console dbal:run-sql "SELECT 1" >/dev/null 2>&1; do
  sleep 2
done

echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

#echo "Loading foundry stories"
#php bin/console foundry:load-story -n demoStory

exec apache2-foreground