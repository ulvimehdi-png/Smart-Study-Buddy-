#!/bin/sh
set -euo

echo "Installing composer dependencies..."
composer install --no-interaction --prefer-dist

/docker/scripts/wait-for-db.sh

echo "Running migrations..."
php bin/console doctrine:migrations:migrate \
  --env=test \
  --no-interaction

echo "Loading fixtures..."
php bin/console doctrine:fixtures:load \
  --env=test \
  --no-interaction

echo "Running PHPUnit..."
php bin/phpunit