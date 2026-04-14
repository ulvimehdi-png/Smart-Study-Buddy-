# Development Setup

## Requirements

* Docker + Docker Compose
* `make`

---

## Usage

All commands are run from the project root using:

```bash
make -C docker <command>
```

### Available commands

```bash
make -C docker dev       # Start full dev environment
make -C docker down      # Stop containers
make -C docker reset     # Stop and remove volumes

make -C docker logs      # Follow logs
make -C docker bash      # Open shell in PHP container
make -C docker console   # Run Symfony console

make -C docker install   # Install dependencies
make -C docker migrate   # Run database migrations
```

---

## Environment variables (optional)

You can create a local `.env` file in `./docker/`

```bash
UID=1000
GID=1000
```

Adjust these values if needed to match your system user/group IDs:

```bash
id -u   # your UID
id -g   # your GID
```

This helps prevent permission issues with mounted volumes.

---

## Services

After running `dev`, services are available at:

* Frontend: http://localhost:5173
* Backend: http://localhost:8000

---

## Notes

* The Makefile lives in `./docker/`
* Always run commands from the project root
* Uses Docker Compose under the hood
