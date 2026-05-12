.DEFAULT_GOAL := help

.PHONY: help test-up test-down test-build

help:
	@echo ""
	@echo "Available commands in current directory { "$(PWD)" }"
	@echo ""
	@echo "	--> make test-up"
	@echo "	--> make test-down"
	@echo "	--> make test-build"

test-up:
	$(MAKE) test-down

	@echo "Starting testing environment"
	docker compose up --abort-on-container-exit --exit-code-from php

#	@echo "Running frontend tests"
#	docker compose run --rm php sh -c "cd /app/frontend && npm install && npm run test"

test-down:
	@echo "Stopping testing environment"
	docker compose down -v --remove-orphans

test-build:
	@echo "Building testing environment"
	docker compose build
