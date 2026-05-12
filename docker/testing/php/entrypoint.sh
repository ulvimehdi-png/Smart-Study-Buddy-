#!/bin/sh
set -e

echo "Waiting for database through Doctrine"
until php bin/console dbal:run-sql "SELECT 1"; do
  sleep 2
done

echo "Running migrations"
php bin/console doctrine:migrations:migrate --env=test --no-interaction

echo "Loading fixtures"
php bin/console doctrine:fixtures:load --env=test --no-interaction

echo "Running PHPUnit tests"
php bin/phpunit