# Trustindex-mini

A Dockerized Symfony application for submitting and browsing public company reviews, searching by company name, and comparing company-level review statistics. The user interface is in Hungarian.

## Features

- Validated 1–5 star review submission
- Public review list and detail pages without exposed author emails
- Review count and average rating per company, ordered by average
- Case-insensitive company search
- CSRF protection, honeypot and keyword spam checks, and rate limiting
- Hungarian development fixtures
- Unit, integration, and functional tests

## Stack

PHP 8.2, Symfony 7.4, Doctrine ORM and Migrations, PostgreSQL 16, Twig, PHPUnit 11, PHPStan, PHP-CS-Fixer, Docker Compose, Nginx, and PHP-FPM.

## Quick start

Requirements: Docker with Docker Compose and Make. Host PHP, Composer, PostgreSQL, Node.js, and Symfony CLI are not required.

```bash
cp .env.example .env
# Replace APP_SECRET and the database-password placeholders.
# POSTGRES_PASSWORD and DB_PASSWORD must match.

make init
```

`make init` is the complete Docker setup command. It creates `.env` when missing, builds and starts the containers, installs Composer dependencies, creates the development database, and runs all pending migrations. Run `make db-fixtures` afterward only if sample data is wanted.

Open <http://localhost:8080>.

Useful commands:

```bash
make up                 # Start existing containers
make down               # Stop containers
make composer-install   # Install locked Composer dependencies
make db-create          # Create the development database
make db-migrate         # Run pending migrations
make db-validate        # Validate Doctrine mapping and schema
make db-fixtures        # Replace development data with sample reviews
make test               # Run PHPUnit with the isolated test database
make phpstan            # Run static analysis
make cs-check           # Check coding style
make code-quality       # Run all quality gates
```

`make db-fixtures` purges current development data before inserting samples. Real environment files are ignored; only `.env.example` is committed.

The host-based alternative, when PHP, Composer, Symfony CLI, and PostgreSQL are installed locally, starts with:

```bash
composer install
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate --no-interaction
symfony server:start
```

Local execution requires a PostgreSQL configuration reachable from the host.

## Design notes

- Forms bind to `ReviewInputDto`, keeping incomplete input separate from persistence.
- `ReviewMapper` creates entities and safe `PublicReview` projections.
- Author email is persisted as required but never included in the public read model.
- PostgreSQL performs company grouping, counting, averaging, and sorting.
- Substring search uses bound parameters and a matching `pg_trgm` GIN index.
- Rating integrity is protected by validation, an entity invariant, and a database constraint.

The source layout is intentionally layer-oriented because this is one cohesive feature with a 4–6-hour scope. Detailed decisions are in [ARCHITECTURE.md](ARCHITECTURE.md), while [SCALING.md](SCALING.md) discusses higher-load evolution.

At production scale, a modular monolith could organize code by business capability:

```text
src/
├── Review/
│   ├── Domain/
│   │   ├── Entity/
│   │   │   └── Review.php
│   │   ├── ValueObject/
│   │   │   └── Rating.php
│   │   └── Exception/
│   ├── Application/
│   │   ├── Command/
│   │   ├── Query/
│   │   ├── Dto/
│   │   └── Service/
│   ├── Infrastructure/
│   │   ├── Persistence/
│   │   │   └── DoctrineReviewRepository.php
│   │   └── Spam/
│   └── Presentation/
│       ├── Controller/
│       ├── Form/
│       └── ReadModel/
├── Company/
│   ├── Domain/
│   ├── Application/
│   ├── Infrastructure/
│   └── Presentation/
└── Shared/
```

This would be accompanied by canonical company identities, pagination, cached or precomputed aggregates, asynchronous moderation and imports, Redis-backed distributed limits, and read replicas when measured load justifies them. Folder nesting alone does not create modularity or scalability; module ownership and enforced dependency direction do.

## Project structure

```text
src/
├── Controller/    HTTP handling
├── DataFixtures/  Development sample data
├── Dto/           Validated form input
├── Entity/        Doctrine model
├── Enum/          Classification outcomes
├── Exception/     Submission rejection
├── Form/          Symfony form definition
├── Mapper/        Model conversion
├── ReadModel/     Typed public output
├── Repository/    Persistence and queries
└── Service/       Submission and spam behavior
```

## Before submission

```bash
docker compose config --quiet
make composer-install
make db-create
make db-migrate
make db-validate
docker compose exec -T php composer validate --strict
docker compose exec -T php composer audit --locked
make test
make phpstan
make cs-check
git status --short
```

Manually verify the exact `Köszönjük a véleményed!` success message, Hungarian validation, review truncation/details, company ordering, search, spam rejection, responsive layout, and absence of public author emails.

## Work log

Times are approximate active working time and exclude breaks. The first subtotal marks the initial working implementation; later rows record optional hardening and refinement.

| Date | Task | Approximate time |
| --- | --- | ---: |
| 2026-08-30 | Requirements and architecture | 15 minutes |
| 2026-08-30 | Symfony, Docker, and tooling | 15 minutes |
| 2026-08-30 | Entity, repository, and migrations | 12 minutes |
| 2026-08-30 | DTO, services, and spam protection | 3 minutes |
| 2026-08-30 | Controller, Twig, and UI | 7 minutes |
| 2026-08-30 | Unit, integration, and functional tests | 5 minutes |
| 2026-08-30 | Documentation and verification | 2 minutes |
| **Initial working implementation** |  | **59 minutes** |
| 2026-08-30 | Privacy and search hardening | 45 minutes |
| 2026-08-30 | Fixtures, Hungarian localization, and UI refinement | 60 minutes |
| 2026-08-30 | Validation fixes, test updates, and code organization | 30 minutes |
| 2026-08-30 | Architecture documentation and manual QA | 45 minutes |
| **Total active time** |  | **Approximately 4 hours** |
