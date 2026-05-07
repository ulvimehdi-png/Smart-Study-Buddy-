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
	make -C docker dev-up

dev-down:
	make -C docker dev-down

test-up:
	make -C docker test-up

test-down:
	make -C docker test-down

prod-up:
	make -C docker prod-up

prod-down:
	make -C docker prod-down