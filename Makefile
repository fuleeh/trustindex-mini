DOCKER_COMPOSE := docker compose
PHP := $(DOCKER_COMPOSE) run --rm php

.PHONY: init env up down build shell composer-install db-create db-migrate db-diff db-validate test-db-create test phpstan cs-fix cs-check code-quality

init: env build up composer-install db-create db-migrate

env:
	@test -f .env || cp .env.example .env

up:
	$(DOCKER_COMPOSE) up -d

down:
	$(DOCKER_COMPOSE) down

build:
	$(DOCKER_COMPOSE) build

shell:
	$(DOCKER_COMPOSE) exec php sh

composer-install:
	$(PHP) composer install --no-interaction

db-create:
	$(PHP) php bin/console doctrine:database:create --if-not-exists

db-migrate:
	$(PHP) php bin/console doctrine:migrations:migrate --no-interaction

db-diff:
	$(PHP) php bin/console doctrine:migrations:diff --no-interaction

db-validate:
	$(PHP) php bin/console doctrine:schema:validate

test-db-create:
	$(PHP) php bin/console doctrine:database:create --env=test --if-not-exists

test: test-db-create
	$(PHP) php bin/phpunit

phpstan:
	$(PHP) vendor/bin/phpstan analyse --memory-limit=512M

cs-fix:
	$(PHP) vendor/bin/php-cs-fixer fix --allow-risky=yes

cs-check:
	$(PHP) vendor/bin/php-cs-fixer fix --dry-run --diff --allow-risky=yes

code-quality: phpstan cs-check test
