.DEFAULT_GOAL := help

.PHONY: help prod-up prod-down

help:
	@echo ""
	@echo "Available commands in current directory { "$(PWD)" }"
	@echo ""
	@echo "	--> make prod-up"
	@echo "	--> make prod-down"
	@echo ""
	@echo "prod-up simulates the deployement web-server setup locally by creating"
	@echo "a custom docker build that in many ways resembles the steps in"
	@echo ".gitlab-ci.yml, thereby making its integration a little easier"
	@echo ""
	@echo "!!! THIS TAKES SOME TIME, SO MAKE SURE THE DEVELOPMENT VERSION WORKS FIRST !!!"
	@echo ""

prod-up:
	$(MAKE) prod-down
	docker compose build --no-cache --pull --progress=plain webserver
	docker compose up --force-recreate -d
	@echo "Reachable outside container:"
	@echo ""
	@echo "Webserver:   (BROWSER)         http://localhost:8080"
	@echo "Database:    (DB VIEWER)       mysql://app:app@database:3308/app?serverVersion=8.0.32&charset=utf8mb4"

prod-down:
	@echo "Stopping current running containers"
	docker compose down -v --remove-orphans