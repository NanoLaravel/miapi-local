up:
	docker compose up -d --build

down:
	docker compose down

migrate:
	docker compose run --rm artisan migrate

seed:
	docker compose run --rm artisan db:seed

test:
	docker compose run --rm artisan test

logs:
	tail -f logs/laravel.log

php:
	docker compose exec php $(cmd)

artisan:
	docker compose exec artisan $(cmd)

composer:
	docker compose exec composer $(cmd)

mysql:
	docker compose exec mysql $(cmd)
