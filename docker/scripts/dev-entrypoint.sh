#!/bin/sh
set -eu

echo "Installing composer dependencies..."
composer install --no-interaction --prefer-dist

echo "Linting config and container..."
php bin/console lint:yaml config
php bin/console lint:container

echo "Clearing caches..."
composer clear-cache --profile
php bin/console cache:clear --env=dev

/scripts/wait-for-db.sh

echo "Checking migration status..."

if php bin/console doctrine:migrations:up-to-date --no-interaction; then
  echo "Database up to date"
else
  if ! php bin/console doctrine:migrations:migrate --no-interaction; then

    # Dev setup fallback to not have to rebuild database every time
    echo "Migration failed — assuming pre-existing schema, syncing state..."

    php bin/console doctrine:migrations:sync-metadata-storage --no-interaction
    php bin/console doctrine:migrations:version --add --all --no-interaction || true
  fi
fi

echo "Loading foundry story..."
php bin/console foundry:load-story baseStory --no-interaction

echo "Starting PHP dev server..."
php -S 0.0.0.0:8000 -t public