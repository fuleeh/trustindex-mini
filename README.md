# Trustindex Review Mini-Application

A Symfony application where visitors can submit and browse public company reviews, compare company-level review statistics, and search by company name.

## Features

- Validated review submission with a 1–5 star rating
- Public review list and detail pages
- Company review counts and average ratings, sorted highest first
- Case-insensitive company search
- CSRF protection, honeypot and keyword spam detection, and submission rate limiting
- Private handling of author email addresses
- PostgreSQL-backed integration and functional tests

## Stack

- PHP 8.2 and Symfony 7.4
- Doctrine ORM and Doctrine Migrations
- PostgreSQL 16
- Twig and local responsive CSS
- PHPUnit 11, PHPStan level 8, and PHP-CS-Fixer
- Docker Compose with Nginx and PHP-FPM

The application is a modular monolith. See [ARCHITECTURE.md](ARCHITECTURE.md) for requirements, interfaces, data flow, design decisions, and deep dives. See [SCALING.md](SCALING.md) for possible evolution under higher load.

## Requirements

- Docker with Docker Compose
- Make

No host PHP, Composer, PostgreSQL, Node.js, or Symfony CLI installation is required.

## Quick start

1. Create the local environment file:

   ```bash
   cp .env.example .env
   ```

2. Replace the placeholder `APP_SECRET` and database password in `.env`. `POSTGRES_PASSWORD` and `DB_PASSWORD` must contain the same local value.

3. Build and initialize the application:

   ```bash
   make init
   ```

4. Open <http://localhost:8080>.

`make init` creates `.env` automatically when it is absent, but copying it explicitly makes the configuration step visible. Real environment files are ignored by Git; only `.env.example` is committed.

To stop the application:

```bash
make down
```

To restart existing containers:

```bash
make up
```

## Docker and Composer commands

Install locked dependencies:

```bash
make composer-install
# Equivalent:
docker compose run --rm php composer install --no-interaction
```

Open a shell in the running PHP container:

```bash
make shell
```

The assignment mentions `composer install` and `symfony serve` as setup examples. This project intentionally uses Docker instead: Composer runs in the PHP container, and Nginx serves the application on port 8080. If PHP, Composer, Symfony CLI, and a reachable PostgreSQL server are installed locally, the conventional alternative is:

```bash
composer install
symfony server:start
```

Local execution also requires overriding the database host and credentials for a PostgreSQL instance reachable from the host.

## Database and migrations

Create the configured database:

```bash
make db-create
```

Run pending migrations:

```bash
make db-migrate
```

Generate a migration after changing Doctrine mapping:

```bash
make db-diff
```

Validate mapping and schema synchronization:

```bash
make db-validate
```

The PostgreSQL initialization script creates an isolated `app_test` database on a fresh volume. For an existing volume, create it idempotently with:

```bash
make test-db-create
```

Changing PostgreSQL initialization credentials after a volume has already been created does not change the existing database user's password. Reuse the original local value or intentionally recreate/update the local database before continuing.

## Testing and code quality

Run PHPUnit:

```bash
make test
# Direct Docker equivalent after the test database exists:
docker compose run --rm php php bin/phpunit
```

Run static analysis:

```bash
make phpstan
```

Check or fix coding style:

```bash
make cs-check
make cs-fix
```

Run the full quality gate:

```bash
make code-quality
```

The suite contains pure unit tests, PostgreSQL repository integration tests for average calculation and ordering, and functional HTTP tests.

## Security and privacy decisions

- Forms bind to `ReviewInputDto`, not directly to the managed Doctrine entity.
- Symfony Form CSRF protection is enabled.
- Twig auto-escaping remains enabled for user content.
- Author email is stored as required but never rendered publicly.
- Spam is rejected before persistence using a visually hidden honeypot and configurable keyword rules.
- Submissions are rate-limited per client IP.
- Spam feedback is generic and does not reveal which rule matched.
- Real `.env` files, dependencies, runtime data, credentials, private keys, and tool caches are excluded from Git and Docker build context.
- The database is accessible only on the internal Compose network by default.

## Project structure

```text
src/
├── Controller/    HTTP request and response handling
├── Dto/           Validated form input
├── Entity/        Doctrine persistence model
├── Exception/     Submission rejection signal
├── Form/          Symfony form definition
├── Mapper/        Input-to-entity conversion
├── ReadModel/     Typed company aggregate results
├── Repository/    Persistence, search, and aggregate queries
└── Service/       Submission orchestration and spam rules
```

## Munkanapló

The following records the active AI-assisted design and implementation session. Review/discussion time is included in the relevant phase; unattended build time is not expanded into estimates.

| Dátum | Feladat | Ráfordított idő |
| --- | --- | ---: |
| 2026-08-30 | Fázis 0 – Követelmények és architektúra | 15 perc |
| 2026-08-30 | Fázis 1 – Symfony/Docker setup és tooling | 15 perc |
| 2026-08-30 | Fázis 2 – Entitás, repository és migráció | 12 perc |
| 2026-08-30 | Fázis 3 – DTO, service és spamvédelem | 3 perc |
| 2026-08-30 | Fázis 4 – Controller, Twig és UI | 7 perc |
| 2026-08-30 | Fázis 5 – Unit, integration és functional tesztek | 5 perc |
| 2026-08-30 | Fázis 6 – Dokumentáció és végső ellenőrzés | 2 perc |
| **Összesen** |  | **59 perc** |

These values should be adjusted if additional manual review, testing, or refinement time is spent before submission.
