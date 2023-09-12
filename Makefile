SHELL := /bin/bash

.PHONY: help
help: ## Show this help.
	@grep -E '^[a-zA-Z0-9 -_]+:.*##'  Makefile | while read -r l; do printf "\033[1;33mmake $$(echo $$l | cut -f 1 -d':')\033[00m:$$(echo $$l | cut -f 3- -d'#')\n"; done


# Container controls
init: ## Initialise the project
	docker compose up -d --build
	docker compose exec php /bin/bash -c 'symfony composer install && \
		symfony console doctrine:wait-for-db && \
		symfony console doctrine:migrations:migrate -n && \
		symfony console doctrine:fixtures:load -n'

destroy: ## Destroy all containers and volumes
	docker compose down -v

reset: destroy init ## Re-initialise containers from scratch


# Utility

shell: ## Open a shell in the php container
	docker compose exec php /bin/bash

migrate: ## Run migrations
	docker compose exec php /bin/bash -c 'symfony console doctrine:migrations:migrate'

fixtures: ## Run migrations
	docker compose exec php /bin/bash -c 'symfony console doctrine:fixtures:load'

# Tests
test_functional: ## Run API and command tests
	docker compose exec php /bin/bash -c 'symfony console doctrine:database:drop --force --env=test || true && \
		symfony console doctrine:database:create --env=test && \
		symfony console doctrine:migrations:migrate -n --env=test && \
		symfony php bin/phpunit tests/Api && \
		symfony php bin/phpunit tests/Command'

test_unit: ## Run unit tests
	docker compose exec php /bin/bash -c 'symfony php bin/phpunit tests/Unit'

test: test_unit test_functional ## Run all tests


lint: ## Run linters
		docker compose exec php /bin/bash -c './vendor/bin/php-cs-fixer fix && \
		vendor/bin/phpstan analyse'