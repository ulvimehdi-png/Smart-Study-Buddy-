.DEFAULT_GOAL := help

.PHONY: help dev-up dev-down dev-help test-up test-down test-help prod-up prod-down prod-help

help:
	@echo ""
	@echo "Available commands in current directory { "$(PWD)" }"
	@echo ""
	@echo "	--> make dev-up"
	@echo "	--> make dev-down"
	@echo "	--> make dev-help"
	@echo ""
	@echo "	--> make prod-up"
	@echo "	--> make prod-down"
	@echo "	--> make prod-help"
	@echo ""
	@echo "	--> make test-up"
	@echo "	--> make test-down"
	@echo "	--> make test-help"
	@echo ""

dev-up:
	make -C docker/development dev-up

dev-down:
	make -C docker/development dev-down

dev-help:
	make -C docker/development help

prod-up:
	make -C docker/production prod-up

prod-down:
	make -C docker/production prod-down

prod-help:
	make -C docker/production help

test-up:
	make -C docker/testing test-up

test-down:
	make -C docker/testing test-down

test-help:
	make -C docker/testing help