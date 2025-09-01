# Executables (local)
DOCKER_COMP = docker compose

# Docker containers
PHP_CONT = $(DOCKER_COMP) exec prestashop-git

# Executables
PHP      = $(PHP_CONT) php
COMPOSER = $(PHP_CONT) composer
SYMFONY  = $(PHP) bin/console

# Misc
.DEFAULT_GOAL = install
.PHONY        : help docker-build docker-up docker-start docker-down docker-logs docker-sh docker-bash composer sf cc test test-unit test-integration assets assets-dev wait-assets admin front admin-default admin-new-theme front-core front-classic front-hummingbird install install-prestashop cs-fixer cs-fixer-dry phpstan scss-fixer es-linter

## —— 🎵 🐳 PrestaShop Docker Makefile 🐳 🎵 ———————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9\./_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
docker-build: ## Builds the Docker images
	COMPOSE_BAKE=true $(DOCKER_COMP) build --pull --no-cache

docker-up: ## Start the docker hub in detached mode (no logs)
	$(DOCKER_COMP) up --detach --force-recreate --remove-orphans

docker-start: docker-build docker-up ## Build and start the containers

docker-down: ## Stop the docker hub
	@$(DOCKER_COMP) down --remove-orphans

docker-logs: ## Show live logs
	@$(DOCKER_COMP) logs --follow

docker-sh: ## Connect to the PHP container as www-data:www-data
	@$(PHP_CONT) runuser -u www-data -g www-data -- sh

docker-bash: ## Connect to the PHP container via bash as www-data:www-data so up and down arrows go to previous commands
	@$(PHP_CONT) runuser -u www-data -g www-data -- bash

## —— PrestaShop 🛒 ———————————————————————————————————————————————————————————
install: composer assets  ## Install PHP dependencies and build the static assets

install-prestashop: ## Install fresh PrestaShop database (requires containers to be running)
	@$(PHP_CONT) .docker/install/database.sh

## —— Assets 🎨 ———————————————————————————————————————————————————————————————
assets: admin front ## Build all assets
	./tools/assets/build.sh all --force

assets-dev: ## Run dev-server for assets
	npx concurrently -c "#93c5fd,#c4b5fd,#fb7185,#fdba74" "cd admin-dev/themes/default && npm run watch" "cd admin-dev/themes/new-theme && npm run watch" "cd themes/classic/_dev && npm run watch" "cd themes/hummingbird && npm run watch" --names=admin-default,admin-new-theme,front-classic,front-hummingbird --kill-others

wait-assets: ## Wait for assets to be built
	./tools/assets/wait-build.sh

admin: ## Build admin assets
	./tools/assets/build.sh admin-default --force & ./tools/assets/build.sh admin-new-theme --force

front: ## Build front assets
	./tools/assets/build.sh front-core --force & ./tools/assets/build.sh front-classic --force & ./tools/assets/build.sh front-hummingbird --force

admin-default: ## Build assets for default admin theme
	./tools/assets/build.sh admin-default --force

admin-new-theme: ## Build assets for new admin theme
	./tools/assets/build.sh admin-new-theme --force

front-core: ## Build assets for core theme
	./tools/assets/build.sh front-core --force

front-classic: ## Build assets for classic theme
	./tools/assets/build.sh front-classic --force

front-hummingbird: ## Build assets for hummingbird theme
	./tools/assets/build.sh front-hummingbird --force

## —— Composer 🧙 ——————————————————————————————————————————————————————————————
composer: ## Install PHP dependencies
	COMPOSER_PROCESS_TIMEOUT=600 composer install --no-interaction
	./bin/console cache:clear --no-warmup

## —— Symfony 🎵 ———————————————————————————————————————————————————————————————
sf: ## List all Symfony commands or pass the parameter "c=" to run a given command, example: make sf c=about
	@$(eval c ?=)
	@$(SYMFONY) $(c)

cc: c=c:c ## Clear the cache
cc: sf

## —— Tests 🧪 —————————————————————————————————————————————————————————————————
test: ## Run all tests
	@$(COMPOSER) run test

test-unit: ## Run unit tests only
	@$(COMPOSER) run unit-tests

test-integration: ## Run integration tests only
	@$(COMPOSER) run integration-tests

## -- Code quality 🧹 ——————————————————————————————————————————————————————————
cs-fixer: ## Run php-cs-fixer
	@$(COMPOSER) run php-cs-fixer

cs-fixer-dry: ## Run php-cs-fixer with dry-run
	@$(COMPOSER) php-cs-fixer:dry

phpstan: ## Run phpstan analysis
	@$(COMPOSER) run phpstan

scss-fixer: ## Run scss-fix
	cd admin-dev/themes/new-theme && npm run scss-fix
	cd admin-dev/themes/default && npm run scss-fix
	cd themes/classic/_dev && npm run scss-fix

es-linter: ## Run lint-fix
	cd admin-dev/themes/new-theme && npm run lint-fix
	cd admin-dev/themes/default && npm run lint-fix
	cd themes/classic/_dev && npm run lint-fix
	cd themes && npm run lint-fix
