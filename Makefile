# -------------------------
# DOCKER COMPOSE HELPERS
# -------------------------

up:
	docker compose up -d --build

down:
	docker compose down

logs:
	tail -f logs/laravel.log

# -------------------------
# LARAVEL ARTISAN
# -------------------------

artisan:
	docker compose run --rm artisan $(filter-out $@,$(MAKECMDGOALS))

migrate:
	docker compose run --rm artisan migrate

migrate-fresh:
	docker compose run --rm artisan migrate:fresh --seed

seed:
	docker compose run --rm artisan db:seed

test:
	docker compose run --rm artisan test

tinker:
	docker compose run --rm artisan tinker

# -------------------------
# COMPOSER
# -------------------------

composer:
	@:

%:
	docker compose run --rm composer  $(MAKECMDGOALS)

# -------------------------
# SHELL / MYSQL
# -------------------------

php:
	docker compose exec php bash

mysql:
	docker compose exec mysql bash


