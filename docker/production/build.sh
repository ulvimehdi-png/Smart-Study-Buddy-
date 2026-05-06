#!/usr/bin/env bash
set -euxo pipefail

./cleanup.sh

# DOCKER CI
docker run -d --name ci-runner composer:2.9.5 sleep infinity

docker exec ci-runner sh -c "mkdir -p /app/backend /app/frontend"
docker cp ../../backend/. ci-runner:/app/backend/
docker cp ../../frontend/. ci-runner:/app/frontend/

docker exec ci-runner sh -c "apk add --no-cache nodejs npm"

# BACKEND
docker exec ci-runner sh -c '
  set -eux

  cd /app/backend

  export APP_ENV=prod
  export APP_DEBUG=0
  export APP_SECRET=ci_dummy_secret
  export DATABASE_URL="mysql://app:app@database:3306/app?serverVersion=8.0.32&charset=utf8mb4"

  composer validate --no-check-publish

  composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --classmap-authoritative

  php bin/console lint:yaml config
  php bin/console lint:container

  php bin/console cache:clear --env=prod --no-debug
  php bin/console cache:warmup --env=prod
'

# FRONTEND
docker exec ci-runner sh -c '
  set -eux

  rm -rf /app/backend/public/assets

  cd /app/frontend

  npm ci
  npm run build
'

docker exec ci-runner sh -c '
  set -eux

  cd /app/backend

  rm -rf \
    .git \
    .gitignore \
    .env.prod \
    .env.dev \
    .env.test \
    phpunit.xml.dist \
    phpunit.dist.xml \
    README.md \
    tests

  mkdir -p var/cache var/log public/upload
'

mkdir -p ./var-www-html
docker cp ci-runner:/app/backend/. ./var-www-html/

docker rm -f ci-runner

docker compose up -d --build webserver database
docker compose exec -T webserver php bin/console doctrine:migrations:migrate --no-interaction --env=prod
#docker compose exec -T