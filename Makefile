.PHONY: \
	help \
	config-check print-ports \
	dev-up dev-down dev-reset dev-smoke \
	test test-down \
	prod-build prod-build-clean prod-up prod-up-detached prod-down \
	prod-reset prod-smoke prod-export prod-artifact \
	dev-logs dev-logs-single test-logs test-logs-single prod-logs prod-logs-single

####################
# GENERAL DEFAULTS #
####################
export
.DEFAULT_GOAL	:= help

SHELL 			:= bash
.SHELLFLAGS 	:= -eu -o pipefail -c

EXPORT_DIR 		?= .deploy

#################
# PROJECT NAMES #
#################
APP_NAME	:= studybuddy
CI_SUFFIX	:= $(if $(CI_PIPELINE_ID),$(CI_PIPELINE_ID),local)
PROD_IMAGE	?= $(APP_NAME):prod-$(CI_SUFFIX)

PROJECT_NAME = $(APP_NAME)_$(1)_$(CI_SUFFIX)

PROJECT_DEV  := $(call PROJECT_NAME,dev)
PROJECT_TEST := $(call PROJECT_NAME,test)
PROJECT_PROD := $(call PROJECT_NAME,prod)

####################
# COMPOSE DEFAULTS #
####################
COMPOSE_BASE		= docker compose --project-directory . --env-file ./compose.env -f docker-compose.yml
COMPOSE_BASE_LOCAL	= $(COMPOSE_BASE) -f docker-compose.local.yml

COMPOSE_RUN			= APP_ENV=$(1) COMPOSE_PROJECT_NAME=$(2) $(3) --profile $(1)

DEV_CMD				:= $(call COMPOSE_RUN,dev,$(PROJECT_DEV),$(COMPOSE_BASE_LOCAL))
TEST_CMD			:= $(call COMPOSE_RUN,test,$(PROJECT_TEST),$(COMPOSE_BASE))
PROD_CMD			:= $(call COMPOSE_RUN,prod,$(PROJECT_PROD),$(COMPOSE_BASE))

EXEC_CMD = $(1) exec -T $(2) $(3)


PROD_LOCAL := APP_ENV=prod COMPOSE_PROJECT_NAME=$(PROJECT_PROD) \
docker compose --project-directory . --env-file ./compose.env \
-f docker-compose.yml -f docker-compose.local.yml --profile prod
#####################
# ENVIRONMENT = DEV #
#####################
dev-endpoints:
	@echo ""
	@echo "Development endpoints:"
	@echo "  Frontend:  http://127.0.0.1:$${EXT_PORT_NODE_DEV:-5173}"
	@echo "  Backend:   http://127.0.0.1:$${EXT_PORT_PHP_DEV:-8001}"
	@echo "  Mercure:   http://127.0.0.1:$${EXT_PORT_MERCURE_DEV:-3000}/.well-known/mercure"
	@echo ""

dev-up:
	$(DEV_CMD) up -d
	$(MAKE) dev-endpoints

dev-down:
	$(DEV_CMD) down

dev-build:
	$(DEV_CMD) build

dev-build-clean:
	$(DEV_CMD) build --no-cache --pull

dev-down-clean:
	$(DEV_CMD) down -v --remove-orphans

dev-shell-php:
	$(call EXEC_CMD,$(DEV_CMD),php-dev,bash)

dev-shell-node:
	$(call EXEC_CMD,$(DEV_CMD),node-dev,sh)

dev-smoke:
	#$(DEV) exec -T php-dev curl -fsS http://127.0.0.1:8000
	$(call EXEC_CMD,$(DEV_CMD),node-dev,wget -qO- http://127.0.0.1:5173 >/dev/null )
	$(call EXEC_CMD,$(DEV_CMD),mercure-dev,wget -qO- http://127.0.0.1/healthz >/dev/null )

dev-reset: dev-down-clean dev-build-clean dev-up dev-endpoints

dev-ci-setup: dev-down-clean dev-build-clean dev-up

dev-ci-test: dev-smoke

dev-ci-teardown: dev-down-clean

dev-ci: dev-ci-setup dev-ci-test dev-ci-teardown

######################
# ENVIRONMENT = TEST #
######################
test: test-down
	$(TEST_CMD) up --build --abort-on-container-exit --exit-code-from php-test-runner
	$(MAKE) test-down

test-down:
	$(TEST_CMD) down -v --remove-orphans

test-ci: test

######################
# ENVIRONMENT = PROD #
######################
prod-up:
	@echo WARNING: this is just the local emulation of the production environment
	$(PROD_LOCAL) up -d
	@echo WARNING: this is just the local emulation of the production environment

prod-down:
	$(PROD_LOCAL) down

prod-down-clean:
	$(PROD_LOCAL) down -v --remove-orphans

prod-build:
	$(PROD_LOCAL) build

prod-build-clean:
	$(PROD_LOCAL) build --no-cache --pull

prod-reset: prod-down-clean prod-build-clean prod-up

#
# -- prod ci -- just to be sure down also includes local
#
prod-ci-setup:
	$(PROD_CMD) down -v --remove-orphans
	$(PROD_CMD) build --no-cache --pull

prod-ci-export:
	rm -rf $(EXPORT_DIR)
	mkdir -p $(EXPORT_DIR)
	@test -n "$(PROD_IMAGE)"
	cid="$$(docker create "$(PROD_IMAGE)")"; \
	trap 'docker rm -f "$$cid" >/dev/null 2>&1 || true' EXIT; \
	docker cp "$$cid:/var/www/html/." $(EXPORT_DIR)/
	docker image rm $(PROD_IMAGE)

prod-ci: prod-ci-setup prod-ci-export

##############
# DEPLOYMENT #
##############
deploy-ci-setup:
	ssh -o BatchMode=yes -o ConnectTimeout=15 "$DEPLOY_HOST" "echo remote-ok"
	@test -f $(EXPORT_DIR)/public/index.html
	@test -f $(EXPORT_DIR)/public/index.php
	@test -f $(EXPORT_DIR)/bin/console
	@test -d $(EXPORT_DIR)/vendor

deploy-ci-rsync:
	rsync -rvz --delete-after \
				--exclude=".env*" \
				--exclude="upload/" \
				--exclude="var/cache/" \
				--exclude="var/log/" \
				"$(EXPORT_DIR)/" $(DEPLOY_HOST):$(DEPLOY_PATH)

# For "real" deployment the env-files should be handled differently,
# but this is the best quick fix i could come up with when a lot is still changing
# and i don't want to manually go check the contents out each time, cheers
deploy-ci-env:
	echo "APP_ENV=prod" | ssh "$DEPLOY_HOST" "cat > '$DEPLOY_PATH/.env'"
	ssh "$DEPLOY_HOST" "cat > '$DEPLOY_PATH/.env.local'" < "$PROD_ENV_LOCAL_FILE"
	ssh "$DEPLOY_HOST" "chmod 644 '$DEPLOY_PATH/.env' '$DEPLOY_PATH/.env.local' && test -f '$DEPLOY_PATH/.env.local'"

# Same thing here, no auto-migrate deployment, but atm it's okay
# because we dont even have real users :'O
deploy-ci-migrate:
	ssh "$DEPLOY_HOST" "\
		set -e; \
		cd '$DEPLOY_PATH'; \
 		php bin/console doctrine:migrations:migrate --no-interaction --env=prod;"

deploy-ci-warm-cache:
	ssh "$DEPLOY_HOST" "\
    		set -e; \
    		cd '$DEPLOY_PATH'; \
    		php bin/console cache:clear --env=prod --no-debug; \
    		php bin/console cache:warmup --env=prod;"

deploy-ci: deploy-ci-setup deploy-ci-rsync deploy-ci-env deploy-ci-migrate deploy-ci-warm-cache

#############
# UTILITIES #
#############
config-check:
	$(DEV_CMD) config -q
	$(TEST_CMD) config -q
	$(PROD_CMD) config -q

print-ports:
	@grep '^EXT_PORT_' $(COMPOSE_ENV) || true

################
# LOGS TARGETS #
################
dev-logs:
	$(eval DEV_SERVICES := php-dev node-dev mercure-dev db-dev)
	$(DEV_CMD) logs -f $(DEV_SERVICES)

dev-logs-single:
	$(DEV_CMD) logs -f $(service)

test-logs-single:
	$(TEST_CMD) logs -f $(service)

test-logs:
	$(eval TEST_SERVICES := php-test-runner db-test)
	$(TEST_CMD) logs -f $(TEST_SERVICES)

prod-logs:
	$(eval PROD_SERVICES := web db-prod)
	$(PROD_CMD) logs -f $(PROD_SERVICES)

prod-logs-single:
	$(PROD_CMD) logs -f $(service)

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
	@echo "  make prod-down"
	@echo "  make prod-reset"
	@echo "  make prod-smoke"
	@echo "  make prod-export"
	@echo "  make prod-artifact"
	@echo "  make prod-logs"
	@echo "  make prod-logs-single"
	@echo "TODO"