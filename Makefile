.PHONY: \
	help \
	dev-up dev-down dev-reset dev-smoke \
	test test-down \
	prod-build prod-build-clean prod-up prod-up-detached prod-down prod-reset prod-smoke prod-export prod-artifact \
	config-check fix-perms print-ports

#####################
# MAKEFILE DEFAULTS #
#####################
.DEFAULT_GOAL := help

SHELL := bash
.SHELLFLAGS := -eu -o pipefail -c

COMPOSE := docker compose --project-directory .

###################
# PORT DEFINITIONS #
###################
EXT_PORT_NODE_DEV		?= 5173
EXT_PORT_PHP_DEV		?= 8001
EXT_PORT_MERCURE_DEV	?= 3000
EXT_PORT_DB_DEV			?= 33061
EXT_PORT_HTTP_PROD		?= 8080
EXT_PORT_DB_PROD		?= 33062

#####################
# ENVIRONMENT = DEV #
#####################
DEV_ENV := \
	COMPOSE_PROJECT_NAME=studybuddy_dev \
	COMPOSE_FILE=./compose.yaml \
	COMPOSE_ENV_FILES=./docker/environment/.env.local.dev

DEV := $(DEV_ENV) $(COMPOSE) --profile dev

dev-up:
	$(DEV) up -d --build
	@echo ""
	@echo "Development endpoints:"
	@echo "  Frontend:  http://127.0.0.1:$(EXT_PORT_NODE_DEV)"
	@echo "  Backend:   http://127.0.0.1:$(EXT_PORT_PHP_DEV)"
	@echo "  Mercure:   http://127.0.0.1:$(EXT_PORT_MERCURE_DEV)/.well-known/mercure"
	@echo "  DB local:  mysql://app:app@127.0.0.1:$(EXT_PORT_DB_DEV)/app_dev?serverVersion=8.0.32&charset=utf8mb4"
	@echo ""

dev-down:
	$(DEV) down

dev-reset:
	$(DEV) down -v
	$(DEV) up -d --build

dev-smoke:
	curl -sS http://127.0.0.1:$(EXT_PORT_PHP_DEV)
	curl -fsS http://127.0.0.1:$(EXT_PORT_NODE_DEV)
	curl -sS http://127.0.0.1:$(EXT_PORT_MERCURE_DEV)/.well-known/mercure

######################
# ENVIRONMENT = TEST #
######################
TEST_ENV := \
	COMPOSE_PROJECT_NAME=studybuddy_test \
	COMPOSE_FILE=./compose.yaml \
	COMPOSE_ENV_FILES=./docker/environment/.env.local.test

TEST := $(TEST_ENV) $(COMPOSE) --profile test

test:
	$(TEST) up --build --abort-on-container-exit --exit-code-from tests

test-down:
	$(TEST) down -v

######################
# ENVIRONMENT = PROD #
######################
PROD_IMAGE ?= studybuddy:prod-emulation

PROD_ENV := \
	COMPOSE_PROJECT_NAME=studybuddy_prod \
	COMPOSE_FILE=./compose.yaml \
	COMPOSE_ENV_FILES=./docker/environment/.env.local.prod

PROD := $(PROD_ENV) $(COMPOSE) --profile prod

prod-build:
	$(PROD) build

prod-build-clean:
	$(PROD) build --no-cache --pull

prod-up:
	$(PROD) up --build

prod-up-detached:
	$(PROD) up -d --build
	@echo ""
	@echo "Production endpoint:"
	@echo "  App:        http://127.0.0.1:$(EXT_PORT_HTTP_PROD)/"
	@echo "  DB local:  mysql://app:app@127.0.0.1:$(EXT_PORT_DB_PROD)/app_prod?serverVersion=8.0.32&charset=utf8mb4"
	@echo ""

prod-up-detached-no-build:
	$(PROD) up -d

prod-down:
	$(PROD) down

prod-reset:
	$(PROD) down -v

prod-smoke:
	curl -fsS http://127.0.0.1:$(EXT_PORT_HTTP_PROD)/

prod-export:
	rm -rf .deploy
	mkdir -p .deploy
	@test -n "$(PROD_IMAGE)"
	docker create --name tmp_export $(PROD_IMAGE)
	docker cp tmp_export:/var/www/html/. .deploy/
	docker rm tmp_export

prod-artifact: prod-build prod-export

#########################
# HELP - DEFAULT TARGET #
#########################
help:
	@echo ""
	@echo "Available commands:"
	@echo ""
	@echo "Development:"
	@echo "  make dev-up"
	@echo "  make dev-down"
	@echo "  make dev-reset"
	@echo "  make dev-smoke"
	@echo ""
	@echo "Testing:"
	@echo "  make test"
	@echo "  make test-down"
	@echo ""
	@echo "Production:"
	@echo "  make prod-build"
	@echo "  make prod-build-clean"
	@echo "  make prod-up"
	@echo "  make prod-up-detached"
	@echo "  make prod-down"
	@echo "  make prod-reset"
	@echo "  make prod-smoke"
	@echo "  make prod-export"
	@echo "  make prod-artifact"
	@echo ""
	@echo "Maintenance:"
	@echo "  make config-check"
	@echo "  make fix-perms"
	@echo "  make print-ports"
	@echo ""

#############
# UTILITIES #
#############
config-check:
	$(DEV) config -q
	$(TEST) config -q
	$(PROD) config -q

#fix-perms:
#	docker run --rm -v "$$(pwd):/w" alpine:3.20 \
#		sh -lc 'chown -R $(HOST_UID):$(HOST_GID) /w 2>/dev/null || true'

print-ports:
	@echo "EXT_PORT_NODE_DEV=$(EXT_PORT_NODE_DEV)"
	@echo "EXT_PORT_PHP_DEV=$(EXT_PORT_PHP_DEV)"
	@echo "EXT_PORT_MERCURE_DEV=$(EXT_PORT_MERCURE_DEV)"
	@echo "EXT_PORT_DB_DEV=$(EXT_PORT_DB_DEV)"
	@echo "EXT_PORT_HTTP_PROD=$(EXT_PORT_HTTP_PROD)"
	@echo "EXT_PORT_DB_PROD=$(EXT_PORT_DB_PROD)"