# Docker Automation Setup

## Requirements

* Docker + Docker Compose
* `make`

---

## Usage

Commands can be run from the directories listed below without 
requiring the old ```make -C docker ...``` syntax. Instead,
use ```make help``` to see all available commands.

- in 

```
- project root
- docker/
- docker/development
- docker/production
- docker/testing
```

---

## Available commands

In either of the above mentioned directories, you can
run ```make help``` to get the available commands.

In either of these directories, all available information will be displayed
when executing the following (simple and exact-same) commands:

```bash
# when in project root
make help

# when in docker/ directory
make help

# when in docker/ subdirectories
make help

# in case of confusion, read the Makefiles in all directories
# and also send me a message
```

---

## Note on data persistence

When using the provided ```make ...-up``` commands, 
all data is loaded from fixtures or foundry stories, 
changes made in the database (MEANING NOT IN CODE) 
will not persist and will be removed the next time
```make ...-up``` or ```make ...-down``` is ran.

* development → BaseStory (mainly deterministic)
* production → DemoStory (TODO later)
* testing → uses it's own fixtures inside the phpunit tests