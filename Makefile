start:
	docker compose up --wait

stop:
	docker compose down

bash:
	docker compose exec php bash

console:
	docker compose exec php bin/console $(cmd)

db-create:
	docker compose exec php bin/console doctrine:database:create

db-create-test:
	docker compose exec php bin/console doctrine:database:create --env=test

migration:
	docker compose exec php bin/console make:migration

migrate:
	docker compose exec php bin/console doctrine:migrations:migrate

migrate-test:
	docker compose exec php bin/console doctrine:migrations:migrate --env=test --no-interaction

fixtures:
	docker compose exec php bin/console doctrine:fixtures:load

cs-fix:
	docker compose exec php vendor/bin/php-cs-fixer fix

phpstan:
	docker compose exec php vendor/bin/phpstan analyse --memory-limit=256M

install:
	docker compose exec php composer install
	sudo chown -R $$USER:$$USER .

grumphp:
	docker compose exec php vendor/bin/grumphp run

entity:
	docker compose exec php bin/console make:entity

cache:
	docker compose exec php bin/console cache:clear

run-tests:
	docker compose exec php bin/phpunit --testdox
