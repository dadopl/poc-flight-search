.PHONY: install test stan cs migrate

install:
	composer install

test:
	php vendor/bin/phpunit

stan:
	php vendor/bin/phpstan analyse --memory-limit=256M

cs:
	php vendor/bin/phpcs

migrate:
	php bin/console doctrine:migrations:migrate --no-interaction

migrate-test:
	APP_ENV=test php bin/console doctrine:migrations:migrate --no-interaction
