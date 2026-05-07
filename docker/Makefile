.DEFAULT_GOAL := help

.PHONY: help dev-up dev-down test-up test-down prod-up prod-down

help:
	@echo ""
	@echo "Available commands in current directory { "$(PWD)" }"
	@echo ""
	@echo "	--> make dev-up"
	@echo "	--> make dev-down"
	@echo "	--> make test-up"
	@echo "	--> make test-down"
	@echo "	--> make prod-up"
	@echo "	--> make prod-down"

dev-up:
	make -C development dev-up

dev-down:
	make -C development dev-down

test-up:
	make -C testing test-up

test-down:
	make -C testing test-down

prod-up:
	make -C production prod-up

prod-down:
	make -C production prod-down