.DEFAULT_GOAL := help

HOST_UID := $(shell id -u)
HOST_GID := $(shell id -g)

SHARED_ENV := --env-file ./docker/environment/shared.env

APP_ENV_DEV := $(SHARED_ENV) --env-file ./docker/environment/dev/dev.app.env
APP_ENV_PROD := $(SHARED_ENV) --env-file ./docker/environment/prod/prod.app.env
APP_ENV_TEST := $(SHARED_ENV) --env-file ./docker/environment/test/test.app.env

COMPOSE := docker compose --project-directory .
BASE := $(COMPOSE) -f ./docker/compose.yaml

DEV := HOST_UID=$(HOST_UID) HOST_GID=$(HOST_GID) COMPOSE_PROJECT_NAME=$${COMPOSE_PROJECT_NAME:-sewt_dev} $(BASE) -f ./docker/compose.dev.yaml $(APP_ENV_DEV)
TEST := HOST_UID=$(HOST_UID) HOST_GID=$(HOST_GID) COMPOSE_PROJECT_NAME=$${COMPOSE_PROJECT_NAME:-sewt_test_$${CI_PIPELINE_ID:-local}} $(BASE) -f ./docker/compose.test.yaml $(APP_ENV_TEST)
PROD := HOST_UID=$(HOST_UID) HOST_GID=$(HOST_GID) COMPOSE_PROJECT_NAME=$${COMPOSE_PROJECT_NAME:-sewt_prod} $(BASE) -f ./docker/compose.prod.yaml $(APP_ENV_PROD)

PROD_IMAGE ?= studybuddy:prod
DEPLOY_ARTIFACT_DIR ?= .deploy

.PHONY: \
	dev-up dev-down dev-reset dev-smoke \
	test test-down \
	prod-up prod-up-detached prod-up-detached-no-build prod-down prod-build prod-build-clean prod-smoke prod-export \
	config-check fix-perms

help:
	@echo ""
	@echo "Available commands:"
	@echo ""
	@echo "  make dev-up		-- Start development environment"
	@echo "  make dev-down		-- Run CI test environment"
	@echo "  make dev-reset		-- Run production simulation"
	@echo ""
	@echo "  make test			-- Stop/remove ALL containers"
	@echo "  make test-down		-- Stop/remove ALL containers & clear ALL docker volumes"
	@echo ""
	@echo "  make prod-up		-- Start development environment"
	@echo "  make prod-down		-- Run CI test environment"
	@echo "  make prod-build	-- Run production simulation"
	@echo ""
	@echo "  make config-check	-- Run CI test environment"
	@echo ""

dev-up:
	$(DEV) up -d
	@echo "Reachable from outside container at:"
	@echo ""
	@echo "Frontend:    (BROWSER)         http://localhost:5173"
	@echo "Backend:     (API)             http://localhost:8000"
	@echo "Database:    (DB VIEWER)       mysql://app:app@database:3307/app?serverVersion=8.0.32&charset=utf8mb4"
	@echo "Mercure:     (AP)              http://localhost:3000/.well-known/mercure"
	@echo "             (BROWSER-DEMO)    http://localhost:3000/.well-known/mercure/ui/"

dev-down:
	$(DEV) down --remove-orphans

dev-reset:
	$(DEV) down -v --remove-orphans
	$(DEV) up --build

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
	@echo "Reachable from outside container at:"
	@echo ""
	@echo "Webserver:   (BROWSER)         http://localhost:8080"
	@echo "Database:    (DB VIEWER)       mysql://app:app@database:3308/app?serverVersion=8.0.32&charset=utf8mb4"

prod-down:
	$(PROD) down -v --remove-orphans

config-check:
	$(DEV) config -q
	$(TEST) config -q
	$(PROD) config -q

dev-smoke:
	@for url in http://127.0.0.1:5173 http://127.0.0.1:8000 http://127.0.0.1:3000/.well-known/mercure; do \
		echo "Checking $$url"; \
		ok=0; \
		for i in $$(seq 1 60); do \
			code=$$(curl -sS -o /dev/null -w "%{http_code}" "$$url" || true); \
			case "$$code" in \
				2*|3*|4*) ok=1; break ;; \
			esac; \
			sleep 2; \
		done; \
		if [ "$$ok" -ne 1 ]; then \
			echo "Service did not become reachable: $$url"; \
			exit 1; \
		fi; \
	done

prod-up-detached:
	$(PROD) up -d --build

prod-smoke:
	@for url in http://127.0.0.1:8080; do \
		echo "Checking $$url"; \
		ok=0; \
		for i in $$(seq 1 60); do \
			code=$$(curl -sS -o /dev/null -w "%{http_code}" "$$url" || true); \
			case "$$code" in \
				2*|3*|4*) ok=1; break ;; \
			esac; \
			sleep 2; \
		done; \
		if [ "$$ok" -ne 1 ]; then \
			echo "Service did not become reachable: $$url"; \
			exit 1; \
		fi; \
	done

prod-export:
	rm -rf "$(DEPLOY_ARTIFACT_DIR)"
	mkdir -p "$(DEPLOY_ARTIFACT_DIR)"
	docker image inspect "$(PROD_IMAGE)" >/dev/null 2>&1 || { \
		echo "Production image not found: $(PROD_IMAGE)"; \
		echo "Run make prod-build first."; \
		exit 1; \
	}
	cid="$$(docker create "$(PROD_IMAGE)")"; \
	docker cp "$$cid:/var/www/html/." "$(DEPLOY_ARTIFACT_DIR)/"; \
	docker rm "$$cid"
	@echo "Exported production filesystem from $(PROD_IMAGE) to $(DEPLOY_ARTIFACT_DIR)"

prod-up-detached-no-build:
	$(PROD) up -d

fix-perms:
	docker run --rm \
		-v "$$(pwd):/work" \
		alpine:3.20 \
		sh -lc 'chown -R $(HOST_UID):$(HOST_GID) /work/backend/vendor /work/frontend/node_modules /work/.deploy 2>/dev/null || true'