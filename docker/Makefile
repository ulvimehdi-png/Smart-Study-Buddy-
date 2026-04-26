.PHONY: dev install migrate wait-db down reset logs bash console

dev:
	@echo "Starting full dev environment..."
	docker compose up -d --build
	$(MAKE) wait-db
	$(MAKE) migrate
	@echo "Ready:"
	@echo "Frontend: http://localhost:8025"
	@echo "Backend: http://localhost:8000"
	@echo "Backend: http://localhost:5173"

wait-db:
	@echo "Waiting for database..."
	until docker compose exec database sh -c "mysql -uapp -papp -h127.0.0.1 app -e 'SELECT 1' >/dev/null 2>&1"; do \
  		sleep 2; \
  	done

migrate:
	@echo "Running migrations..."
	docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction

install:
	@echo "Installing PHP dependencies..."
	docker compose exec php composer install

	@echo "Installing JS dependencies..."
	docker compose run --rm node npm install

down:
	docker compose down

reset:
	docker compose down -v

bash:
	docker compose exec php bash

console:
	docker compose exec php php bin/console

logs:
	docker compose logs -f
