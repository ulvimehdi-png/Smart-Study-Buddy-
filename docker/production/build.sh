#!/usr/bin/env bash
set -euxo pipefail

COMMIT=$(git rev-parse --short HEAD)
BUILD_DIR="artifacts/$COMMIT"

echo "Building commit: $COMMIT"

rm -rf "$BUILD_DIR"
mkdir -p "$BUILD_DIR"

#
# DOCKER CI
#
docker run -d --name ci-runner composer:2.9.5 sleep infinity
docker cp -

#
# BACKEND BUILD
#
echo "Backend build..."
cd backend
composer install --no-dev --no-interaction --optimize-autoloader

#
# FRONTEND BUILD
#
echo "Frontend build..."
cd ../frontend
npm ci
npm run build

#
# DEPLOYING FILES LOCALLY
#

rsync -r --delete-after . "../$BUILD_DIR/backend"

rsync -rvz --delete-after
            --exclude=".git/"
            --exclude=".gitignore"
            --exclude=".env.dev"
            --exclude=".env.test"
            --exclude="phpunit.dist.xml"
            --exclude="README.md"
            --exclude="tests/"
            --exclude="upload/"
            $BACKEND_DIR/
            $DEPLOY_USER@$DEPLOY_HOST:$DEPLOY_PATH/

#
# FRONTEND
#


echo "Build complete in $BUILD_DIR"

#
# DEPLOY LOCALLY
#
echo "Deploying LOCAL build: $COMMIT"

docker compose up -d app database

docker cp "$BUILD_DIR/backend/." app:/var/www/html