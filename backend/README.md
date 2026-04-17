## Composer dependencies

- Base setup (skeleton project)
- Doctrine (ORM, migrations)
- JWT (LexikJWTAuthenticationBundle) for stateless auth
- NelmioCorsBundle for frontend communication

## Fixtures (using Zenstruck Foundry)

### Full database reset
```bash
APP_ENV=dev php bin/console doctrine:database:drop --if-exists --force
APP_ENV=dev php bin/console doctrine:database:create
APP_ENV=dev php bin/console doctrine:migrations:migrate -n
APP_ENV=dev php bin/console foundry:load-fixtures {XXX}
```

### Foundry fixture groups/stories

- Add ```--append``` to keep existing data
- Add ```--purge-with-truncate``` for truncate-based purging


- ```--group=minimal``` 
  - 2 default users (1 normal, 1 admin) ⇒ boot + login
- ```--group=base``` shared seed data, safe default for everyone
- ```--group=dev``` fuller realistic dataset for development
- ```--group=demo``` presentation-friendly dataset
- ```--group=ui``` data to make/break/test frontend pages
- ```--group=large``` larger dataset to test pagination, filtering, and performance edge cases (sort of)

## Commands/working
