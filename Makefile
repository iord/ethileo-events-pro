.PHONY: help install start stop restart logs clean test

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Available targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

install: ## Install dependencies and setup the project
	docker-compose up -d
	@echo "Waiting for MySQL to be ready..."
	@sleep 15
	docker-compose exec wordpress composer install
	docker-compose exec wpcli core install --url=http://localhost:8080 --title="Ethileo Events Pro" --admin_user=admin --admin_password=admin --admin_email=admin@ethileo.local --skip-email
	docker-compose exec wpcli plugin activate ethileo-events-pro
	@echo "Installation complete!"
	@echo "WordPress: http://localhost:8080"
	@echo "Admin: http://localhost:8080/wp-admin (admin/admin)"
	@echo "PHPMyAdmin: http://localhost:8081"

start: ## Start Docker containers
	docker-compose up -d

stop: ## Stop Docker containers
	docker-compose down

restart: ## Restart Docker containers
	docker-compose restart

logs: ## Show Docker logs
	docker-compose logs -f

clean: ## Clean up Docker containers and volumes
	docker-compose down -v
	rm -rf vendor/ node_modules/

test: ## Run tests
	docker-compose exec wordpress composer test

shell: ## Open bash shell in WordPress container
	docker-compose exec wordpress bash

wp: ## Run WP-CLI commands (e.g., make wp ARGS="plugin list")
	docker-compose exec wpcli $(ARGS)

composer: ## Run Composer commands (e.g., make composer ARGS="require package/name")
	docker-compose exec wordpress composer $(ARGS)

npm: ## Run NPM commands (e.g., make npm ARGS="install")
	docker-compose exec wordpress npm $(ARGS)
