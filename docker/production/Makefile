.PHONY: prod-up prod-down

prod-up:
	$(MAKE) prod-down
	docker compose build --no-cache --pull --progress=plain webserver
	docker compose up --force-recreate -d

prod-down:
	@echo "Stopping current running containers"
	docker compose down -v --remove-orphans