.PHONY: \
	help \
	config-check print-ports \
	dev-up dev-down dev-reset dev-smoke \
	test test-down \
	prod-build prod-build-clean prod-up prod-up-detached prod-down \
	prod-reset prod-smoke prod-export prod-artifact prod-migrate \
	dev-logs dev-logs-single test-logs test-logs-single prod-logs prod-logs-single

####################
# GENERAL DEFAULTS #
####################
.DEFAULT_GOAL	:= help

SHELL 			:= bash
.SHELLFLAGS 	:= -eu -o pipefail -c

PROJECT_BASE	?= studybuddy
PROD_IMAGE 		?= studybuddy:prod-emulation

CI_SUFFIX		:= $(if $(CI_PIPELINE_ID),$(CI_PIPELINE_ID),local)

####################
# COMPOSE DEFAULTS #
####################
COMPOSE_ENV		?= ./compose.env
COMPOSE_BASE	:= docker compose --project-directory . --env-file $(COMPOSE_ENV) -f ./docker-compose.yml
COMPOSE_LOCAL	:= $(COMPOSE_BASE) -f ./docker-compose.local.yml

DEV_PROJECT		?= $(PROJECT_BASE)_dev
TEST_PROJECT	?= $(PROJECT_BASE)_test_$(CI_SUFFIX)
PROD_PROJECT	?= $(PROJECT_BASE)_prod_$(CI_SUFFIX)

DEV				:= APP_ENV=dev	COMPOSE_PROJECT_NAME=$(DEV_PROJECT) $(COMPOSE_LOCAL) --profile dev
TEST			:= APP_ENV=test	COMPOSE_PROJECT_NAME=$(TEST_PROJECT) $(COMPOSE_BASE) --profile test
PROD			:= APP_ENV=prod	COMPOSE_PROJECT_NAME=$(PROD_PROJECT) $(COMPOSE_BASE) --profile prod

#####################
# ENVIRONMENT = DEV #
#####################
dev-up:
	$(DEV) run --rm php-dev composer install
	$(DEV) up -d --build
	@echo ""
	@echo "Development endpoints:"
	@echo "  Frontend:  http://127.0.0.1:$${EXT_PORT_NODE_DEV:-5173}"
	@echo "  Backend:   http://127.0.0.1:$${EXT_PORT_PHP_DEV:-8001}"
	@echo "  Mercure:   http://127.0.0.1:$${EXT_PORT_MERCURE_DEV:-3000}/.well-known/mercure"
	@echo ""

dev-down:
	$(DEV) down

dev-reset:
	$(DEV) down -v
	$(DEV) run --rm php-dev composer install
	$(DEV) up -d --build
	@echo ""
	@echo "Development endpoints:"
	@echo "  Frontend:  http://127.0.0.1:$${EXT_PORT_NODE_DEV:-5173}"
	@echo "  Backend:   http://127.0.0.1:$${EXT_PORT_PHP_DEV:-8001}"
	@echo "  Mercure:   http://127.0.0.1:$${EXT_PORT_MERCURE_DEV:-3000}/.well-known/mercure"
	@echo ""

dev-smoke:
	#$(DEV) exec -T php-dev curl -fsS http://127.0.0.1:8000
	$(DEV) exec -T node-dev wget -qO- http://127.0.0.1:5173 >/dev/null
	$(DEV) exec -T mercure-dev wget -qO- http://127.0.0.1/healthz >/dev/null

dev-shell-php:
	$(DEV) exec php-dev bash

dev-shell-node:
	$(DEV) exec node-dev sh

######################
# ENVIRONMENT = TEST #
######################
test:
	#$(TEST) up --build --abort-on-container-exit --exit-code-from php-unit
	$(TEST) up --build --abort-on-container-exit --exit-code-from php-test-runner

test-down:
	$(TEST) down -v

######################
# ENVIRONMENT = PROD #
######################
prod-build:
	$(PROD) build

prod-build-clean:
	$(PROD) build --no-cache --pull

prod-up:
	$(PROD) -f ./docker-compose.local.yml up --build

prod-down:
	$(PROD) down

prod-up-detached:
	$(PROD) up -d --build

prod-up-detached-local:
	COMPOSE_PROJECT_NAME=$(PROD_PROJECT) $(COMPOSE_LOCAL) --profile prod up -d --build

prod-reset:
	$(PROD) down -v --remove-orphans

prod-smoke:
	$(PROD) exec -T web curl -fsS http://127.0.0.1/

prod-smoke-local:
	COMPOSE_PROJECT_NAME=$(PROD_PROJECT) $(COMPOSE_LOCAL) --profile prod exec -T web curl -fsS http://127.0.0.1/

prod-migrate:
	$(PROD) run --rm migrate-prod

prod-export:
	rm -rf .deploy
	mkdir -p .deploy
	@test -n "$(PROD_IMAGE)"
	cid="$$(docker create "$(PROD_IMAGE)")"; \
	trap 'docker rm -f "$$cid" >/dev/null 2>&1 || true' EXIT; \
	docker cp "$$cid:/var/www/html/." .deploy/

prod-artifact: prod-build prod-export

#############
# UTILITIES #
#############
config-check:
	$(DEV) config -q
	$(TEST) config -q
	$(PROD) config -q
	COMPOSE_PROJECT_NAME=$(PROJECT_BASE)_localcheck $(COMPOSE_LOCAL) --profile dev config -q
	COMPOSE_PROJECT_NAME=$(PROJECT_BASE)_localcheck $(COMPOSE_LOCAL) --profile prod config -q

print-ports:
	@grep '^EXT_PORT_' $(COMPOSE_ENV) || true

################
# LOGS TARGETS #
################
define tail-logs
	@project_name=$$(COMPOSE_PROJECT_NAME=$1 $(COMPOSE_BASE) ps --services); \
	if [ -n "$$2" ]; then \
		$(COMPOSE_BASE) --project-name $$1 logs -f $$2; \
	else \
		$(COMPOSE_BASE) --project-name $$1 logs -f $$${project_name}; \
	fi
endef

dev-logs:
	$(eval DEV_SERVICES := php-dev node-dev mercure-dev db-dev)
	$(DEV) logs -f $(DEV_SERVICES)

dev-logs-single:
	$(DEV) logs -f $(service)

test-logs-single:
	$(TEST) logs -f $(service)

test-logs:
	$(eval TEST_SERVICES := php-unit db-test)
	$(TEST) logs -f $(TEST_SERVICES)

prod-logs:
	$(eval PROD_SERVICES := web db-prod)
	$(PROD) logs -f $(PROD_SERVICES)

prod-logs-single:
	$(PROD) logs -f $(service)

#########################
# HELP - DEFAULT TARGET #
#########################
help:
	@echo ""
	@echo "Development:"
	@echo "  make dev-up"
	@echo "  make dev-down"
	@echo "  make dev-reset"
	@echo "  make dev-smoke"
	@echo "  make dev-logs"
	@echo "  make dev-logs-single"
	@echo ""
	@echo "Testing:"
	@echo "  make test"
	@echo "  make test-down"
	@echo "  make test-logs"
	@echo "  make test-logs-single"
	@echo ""
	@echo "Production emulation / CI:"
	@echo "  make prod-build"
	@echo "  make prod-build-clean"
	@echo "  make prod-up"
	@echo "  make prod-up-detached"
	@echo "  make prod-migrate"
	@echo "  make prod-down"
	@echo "  make prod-reset"
	@echo "  make prod-smoke"
	@echo "  make prod-export"
	@echo "  make prod-artifact"
	@echo "  make prod-logs"
	@echo "  make prod-logs-single"
	@echo ""