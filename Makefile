build:
	docker-compose build laravel --pull

setup:
	make build
	make up
	sleep 2
	docker-compose exec laravel php artisan migrate
	docker-compose exec laravel php artisan db:seed

restart:
	make stop
	make up

up:
	docker-compose --env-file ./.env up -d

stop:
	docker-compose stop laravel

test:
	docker-compose exec laravel ./vendor/bin/phpunit --testdox


shell:
	docker-compose exec laravel sh

db_shell:
	docker-compose exec database mysql --user=root -pdb_password app_db