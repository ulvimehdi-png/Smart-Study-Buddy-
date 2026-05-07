#!/usr/bin/env bash
set -euxo pipefail

sudo rm -rf ./var-www-html
docker rm -f ci-runner 2>/dev/null || true

docker compose down -v