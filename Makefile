SHELL := /bin/bash
.SHELLFLAGS := -euo pipefail -c

.DEFAULT_GOAL := help

HOST_UID := $(shell id -u)
HOST_GID := $(shell id -g)

PROD_IMAGE ?= studybuddy:prod
DEPLOY_ARTIFACT_DIR ?= .deploy

EXT_PORT_NODE_DEV ?= 5173
EXT_PORT_PHP_DEV ?= 8000
EXT_PORT_MERCURE_DEV ?= 3000
EXT_PORT_DB_DEV ?= 3307

EXT_PORT_HTTP_PROD ?= 8080
EXT_PORT_DB_PROD ?= 3308

DEV_FRONTEND_URL := http://127.0.0.1:$(EXT_PORT_NODE_DEV)
DEV_BACKEND_URL := http://127.0.0.1:$(EXT_PORT_PHP_DEV)
DEV_MERCURE_URL := http://127.0.0.1:$(EXT_PORT_MERCURE_DEV)/.well-known/mercure

PROD_HTTP_URL := http://127.0.0.1:$(EXT_PORT_HTTP_PROD)

export HOST_UID HOST_GID
export PROD_IMAGE DEPLOY_ARTIFACT_DIR
export EXT_PORT_NODE_DEV EXT_PORT_PHP_DEV EXT_PORT_MERCURE_DEV EXT_PORT_DB_DEV
export EXT_PORT_HTTP_PROD EXT_PORT_DB_PROD

SHARED_ENV := --env-file ./docker/environment/shared.env

APP_ENV_DEV := $(SHARED_ENV) --env-file ./docker/environment/dev/dev.app.env
APP_ENV_PROD := $(SHARED_ENV) --env-file ./docker/environment/prod/prod.app.env
APP_ENV_TEST := $(SHARED_ENV) --env-file ./docker/environment/test/test.app.env

COMPOSE := docker compose --project-directory .
BASE := $(COMPOSE) -f ./docker/compose.yaml

DEV := COMPOSE_PROJECT_NAME=$${COMPOSE_PROJECT_NAME:-sewt_dev} $(BASE) -f ./docker/compose.dev.yaml $(APP_ENV_DEV)
TEST := COMPOSE_PROJECT_NAME=$${COMPOSE_PROJECT_NAME:-sewt_test_$${CI_PIPELINE_ID:-local}} $(BASE) -f ./docker/compose.test.yaml $(APP_ENV_TEST)
PROD := COMPOSE_PROJECT_NAME=$${COMPOSE_PROJECT_NAME:-sewt_prod} $(BASE) -f ./docker/compose.prod.yaml $(APP_ENV_PROD)

PROD_IMAGE ?= studybuddy:prod
DEPLOY_ARTIFACT_DIR ?= .deploy

.PHONY: \
	help \
	dev-up dev-down dev-reset dev-smoke \
	test test-down \
	prod-build prod-build-clean prod-up prod-up-detached prod-down prod-reset prod-smoke prod-export prod-artifact \
	config-check fix-perms print-ports

help:
	@echo ""
	@echo "Available commands:"
	@echo ""
	@echo "Development:"
	@echo "  make dev-up              Start development stack"
	@echo "  make dev-down            Stop development stack"
	@echo "  make dev-reset           Rebuild development stack from scratch"
	@echo "  make dev-smoke           Check development HTTP endpoints"
	@echo ""
	@echo "Testing:"
	@echo "  make test                Run test stack"
	@echo "  make test-down           Remove test stack and volumes"
	@echo ""
	@echo "Production simulation:"
	@echo "  make prod-build          Build production image"
	@echo "  make prod-build-clean    Build production image without cache"
	@echo "  make prod-up             Start production stack in foreground"
	@echo "  make prod-up-detached    Start production stack detached"
	@echo "  make prod-down           Stop production stack, keep volumes"
	@echo "  make prod-reset          Stop production stack and remove volumes"
	@echo "  make prod-smoke          Check production HTTP endpoint"
	@echo "  make prod-export         Export production filesystem to $(DEPLOY_ARTIFACT_DIR)"
	@echo "  make prod-artifact       Build and export production artifact"
	@echo ""
	@echo "Maintenance:"
	@echo "  make config-check        Validate all Compose configurations"
	@echo "  make fix-perms           Repair common host-owned generated directories"
	@echo ""

define wait_http
	@url="$(1)"; \
	tries="$(if $(2),$(2),60)"; \
	echo "Checking $$url"; \
	ok=0; \
	for i in $$(seq "$$tries"); do \
		code=$$(curl -sS -o /dev/null -w "%{http_code}" "$$url" || true); \
		case "$$code" in \
			2*|3*|4*) ok=1; break ;; \
		esac; \
		sleep 1; \
	done; \
	if [ "$$ok" -ne 1 ]; then \
		echo "Service did not become reachable: $$url"; \
		exit 1; \
	fi
endef

dev-up:
	$(DEV) up -d --build
	@echo ""
	@echo "Development endpoints:"
	@echo "  Frontend:  $(DEV_FRONTEND_URL)"
	@echo "  Backend:   $(DEV_BACKEND_URL)"
	@echo "  Mercure:   $(DEV_MERCURE_URL)"
	@echo "  DB local:  mysql://app:app@127.0.0.1:$(EXT_PORT_DB_DEV)/app?serverVersion=8.0.32&charset=utf8mb4"
dev-down:
	$(DEV) down --remove-orphans

dev-reset:
	$(DEV) down -v --remove-orphans
	$(DEV) up -d --build

dev-smoke:
	$(call wait_http,$(DEV_FRONTEND_URL),180)
	$(call wait_http,$(DEV_BACKEND_URL),60)
	$(call wait_http,$(DEV_MERCURE_URL),60)

test:
	$(TEST) up --build --abort-on-container-exit --exit-code-from php

test-down:
	$(TEST) down -v --remove-orphans

prod-build:
	$(PROD) build

prod-build-clean:
	$(PROD) build --no-cache --pull

prod-up:
	$(PROD) up --build

prod-up-detached:
	$(PROD) up -d --build

prod-down:
	$(PROD) down --remove-orphans

prod-reset:
	$(PROD) down -v --remove-orphans

prod-smoke:
	$(call wait_http,http://127.0.0.1:8080)

prod-export:
	rm -rf "$(DEPLOY_ARTIFACT_DIR)"
	mkdir -p "$(DEPLOY_ARTIFACT_DIR)"
	docker image inspect "$(PROD_IMAGE)" >/dev/null 2>&1 || { \
		echo "Production image not found: $(PROD_IMAGE)"; \
		echo "Run make prod-build first."; \
		exit 1; \
	}
	cid=""; \
	trap 'test -z "$$cid" || docker rm -f "$$cid" >/dev/null 2>&1 || true' EXIT; \
	cid="$$(docker create "$(PROD_IMAGE)")"; \
	docker cp "$$cid:/var/www/html/." "$(DEPLOY_ARTIFACT_DIR)/"; \
	docker rm "$$cid"; \
	cid=""
	@echo "Exported $(PROD_IMAGE) filesystem to $(DEPLOY_ARTIFACT_DIR)"

prod-artifact: prod-build prod-export

config-check:
	$(DEV) config -q
	$(TEST) config -q
	$(PROD) config -q

fix-perms:
	docker run --rm \
		-v "$$(pwd):/work" \
		alpine:3.20 \
		sh -lc 'chown -R $(HOST_UID):$(HOST_GID) /work/backend/vendor /work/frontend/node_modules /work/backend/var /work/.deploy 2>/dev/null || true'

print-ports:
	@echo "EXT_PORT_NODE_DEV=$(EXT_PORT_NODE_DEV)"
	@echo "EXT_PORT_PHP_DEV=$(EXT_PORT_PHP_DEV)"
	@echo "EXT_PORT_MERCURE_DEV=$(EXT_PORT_MERCURE_DEV)"
	@echo "EXT_PORT_DB_DEV=$(EXT_PORT_DB_DEV)"
	@echo "EXT_PORT_HTTP_PROD=$(EXT_PORT_HTTP_PROD)"
	@echo "EXT_PORT_DB_PROD=$(EXT_PORT_DB_PROD)"
	@echo "DEV_FRONTEND_URL=$(DEV_FRONTEND_URL)"
	@echo "DEV_BACKEND_URL=$(DEV_BACKEND_URL)"
	@echo "DEV_MERCURE_URL=$(DEV_MERCURE_URL)"
	@echo "PROD_HTTP_URL=$(PROD_HTTP_URL)"