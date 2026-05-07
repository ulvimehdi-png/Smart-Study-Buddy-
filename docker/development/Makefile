.DEFAULT_GOAL := help

.PHONY: help dev-up dev-down

help:
	@echo ""
	@echo "Available commands in current directory { "$(PWD)" }"
	@echo ""
	@echo "	--> make dev-up"
	@echo "	--> make dev-down"
	@echo ""
	@echo "dev-up is the development docker compose setup consisting of four containers:"
	@echo "	- php"
	@echo "		--> php server (8.4.18) with symfony-cli, composer (2.9.5), xdebug"
	@echo "		--> repo directory backend/ is bind mount internally to /app"
	@echo "		--> to get inside the container to run commands, execute:"
	@echo "				[docker compose exec php bash]"
	@echo ""
	@echo "	- node"
	@echo "		--> node container/vite server for easy frontend hot reloading"
	@echo "		--> repo directory frontend/ is bind mount internally to /app"
	@echo "		--> to get inside the container to run commands, execute:"
	@echo "				[docker compose exec node bash]"
	@echo ""
	@echo "	- database"
	@echo "		--> mysql server (8.0.32)"
	@echo "		--> no bind mount, docker volume (database_data)"
	@echo "				!!! with 'make dev-up/dev-down' all data is lost & regenerated (foundry stories) every time !!!"
	@echo "		--> configuration through environment variables in compose.yaml"
	@echo "		--> to get inside the container to run commands, execute:"
	@echo "				[docker compose exec database bash]"
	@echo ""
	@echo "	- mercure"
	@echo "		--> protocol and server designed for pushing real-time updates from servers to clients using SSE"
	@echo "		--> no bind mount, docker volumes (mercure_data, mercure_config), it's a custom build of Caddy"
	@echo "		--> configuration through environment variables in compose.yaml (!! KEYS !!)"
	@echo "		--> to get inside the container to run commands, execute:"
	@echo "				[docker compose exec mercure bash]"
	@echo ""

dev-up:
	$(MAKE) dev-down
	@echo "Starting full dev environment..."
	docker compose up -d --build

	@echo "Installing PHP dependencies..."
	docker compose exec -T php composer install --prefer-source

	@echo "Installing JS dependencies..."
	docker compose run --rm node npm install

	@echo "Waiting for database through Doctrine..."
	until docker compose exec -T php php bin/console dbal:run-sql "SELECT 1" >/dev/null 2>&1; do \
		sleep 1; \
	done

	@echo "Running migrations..."
	docker compose exec -T php php bin/console doctrine:migrations:migrate --no-interaction

	@echo "Loading foundry baseStory fake data"
	docker compose exec -T php php bin/console foundry:load-story -n baseStory

	@echo "Reachable outside container:"
	@echo ""
	@echo "Frontend:    (BROWSER)         http://localhost:5173"
	@echo "Backend:     (API)             http://localhost:8000"
	@echo "Database:    (DB VIEWER)       mysql://app:app@database:3307/app?serverVersion=8.0.32&charset=utf8mb4"
	@echo "Mercure:     (AP)              http://localhost:3000/.well-known/mercure"
	@echo "             (BROWSER-DEMO)    http://localhost:3000/.well-known/mercure/ui/"

dev-down:
	@echo "Stopping current running containers"
	docker compose down -v --remove-orphans