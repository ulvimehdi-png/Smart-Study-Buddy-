# Install dependencies
install:
	python -m venv .venv
	.venv/bin/pip install --upgrade pip
	.venv/bin/pip install -r requirements.txt

# Run migrations
migrate:
	.venv/bin/alembic upgrade head

# Run tests
test:
	.venv/bin/pytest -q --disable-warnings

# Run dev server
run:
	.venv/bin/uvicorn app.main:app --reload

# Reset DB (dangerous, dev only)
reset-db:
	dropdb mydb && createdb mydb
	.venv/bin/alembic upgrade head