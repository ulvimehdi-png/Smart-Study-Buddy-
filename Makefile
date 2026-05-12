.DEFAULT_GOAL := help

define COMPOSE_CMD
docker compose \
	--project-directory . \
	--project-name sewt_$(1)_compose \
	--file ./docker/compose.yaml \
	--file ./docker/compose.$(1).yaml
endef

.PHONY: help dev dev-build test prod down reset

help:
	@echo ""
	@echo "Available commands:"
	@echo ""
	@echo "  make dev	-- Start development environment"
	@echo "  make test	-- Run CI test environment"
	@echo "  make prod	-- Run production simulation"
	@echo ""
	@echo "  make down	-- Stop/remove ALL containers"
	@echo "  make reset	-- Stop/remove ALL containers & clear ALL docker volumes"
	@echo ""
	@echo "  make dev-build	-- (Re)build dev container, probably only useful after changes in *.ini/*.yaml files"
	@echo ""

dev:
	$(call COMPOSE_CMD,dev) up -d
	@echo "Reachable from outside container at:"
	@echo ""
	@echo "Frontend:    (BROWSER)         http://localhost:5173"
	@echo "Backend:     (API)             http://localhost:8000"
	@echo "Database:    (DB VIEWER)       mysql://app:app@database:3307/app?serverVersion=8.0.32&charset=utf8mb4"
	@echo "Mercure:     (AP)              http://localhost:3000/.well-known/mercure"
	@echo "             (BROWSER-DEMO)    http://localhost:3000/.well-known/mercure/ui/"

dev-build:
	$(call COMPOSE_CMD,dev) build

test:
	$(call COMPOSE_CMD,test) up --build --abort-on-container-exit --exit-code-from php

prod:
	$(call COMPOSE_CMD,prod) up --build -d
	@echo "Reachable from outside container at:"
	@echo ""
	@echo "Webserver:   (BROWSER)         http://localhost:8080"
	@echo "Database:    (DB VIEWER)       mysql://app:app@database:3308/app?serverVersion=8.0.32&charset=utf8mb4"

down:
	$(call COMPOSE_CMD,dev) down
	$(call COMPOSE_CMD,test) down
	$(call COMPOSE_CMD,prod) down

reset:
	$(call COMPOSE_CMD,dev) down -v --remove-orphans
	$(call COMPOSE_CMD,test) down -v --remove-orphans
	$(call COMPOSE_CMD,prod) down -v --remove-orphans