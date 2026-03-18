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

migration:
	docker compose exec php bin/console make:migration

migrate:
	docker compose exec php bin/console doctrine:migrations:migrate

cs-fix:
	docker compose exec php vendor/bin/php-cs-fixer fix

phpstan:
	docker compose exec php vendor/bin/phpstan analyse

install:
	docker compose exec php composer install
	sudo chown -R $$USER:$$USER .

grumphp:
	docker compose exec php vendor/bin/grumphp run

entity:
	docker compose exec php bin/console make:entity
