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
	make -C development dev-up

dev-down:
	make -C development dev-down

dev-help:
	make -C development help

prod-up:
	make -C production prod-up

prod-down:
	make -C production prod-down

prod-help:
	make -C production help

test-up:
	make -C testing test-up

test-down:
	make -C testing test-down

test-build:
	make -C testing test-build

test-help:
	make -C testing help