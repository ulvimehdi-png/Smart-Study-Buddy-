.PHONY: dev-up dev-down

dev-up:
	$(MAKE) dev-down
	@echo "Starting full dev environment..."
	docker compose up -d --build

	@echo "Installing PHP dependencies..."
	docker compose exec -T php composer install

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

	@echo "Ready:"
	@echo "Frontend:	http://localhost:5173"
	@echo "Backend:		http://localhost:8000"
	@echo "Database:	http://localhost:3307"

dev-down:
	@echo "Stopping current running containers"
	docker compose down -v --remove-orphans