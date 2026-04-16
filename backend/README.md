## Composer dependencies

- Base setup (skeleton project)
- Doctrine (ORM, migrations)
- JWT (LexikJWTAuthenticationBundle) for stateless auth
- NelmioCorsBundle for frontend communication

## Dev Database reset (fixtures)

```bash
APP_ENV=dev php bin/console doctrine:database:drop --if-exists --force
APP_ENV=dev php bin/console doctrine:database:create
APP_ENV=dev php bin/console doctrine:migrations:migrate -n
APP_ENV=dev php bin/console doctrine:fixtures:load --group=base --group=dev -n
```

- Add ```--append``` to keep existing data
- Add ```--purge-with-truncate``` for truncate-based purging
- Groups: 
  - ```--group=minimal``` one user, very little data, just enough to boot and log in
  - ```--group=base``` shared seed data, safe default for everyone
  - ```--group=dev``` fuller realistic dataset for development
  - ```--group=demo``` presentation-friendly dataset
  - ```--group=ui``` data to make/break/test frontend pages
  - ```--group=large``` larger dataset to test pagination, filtering, and performance edge cases (sort of)
