#!make
include .env
export $(shell sed 's/=.*//' .env)

up:
	make start

start:
	@echo "Starting up containers"
	docker-compose pull
	docker-compose up -d --build --remove-orphans
	@echo You to build the project before using Jenkins, run: make build

build:
	# Install composer
	docker exec -it ${PROJECT_NAME}_appserver sh -c "cd /behat && composer install"

stop:
	@echo "Stopping containers"
	@docker-compose stop

open:
	open "http://127.0.0.1:8080/"

in:
	docker exec -it ${PROJECT_NAME} bash

test:
	# Run test outside Jenkins for debugging
	docker exec -t ${PROJECT_NAME}_appserver sh -c 'cd /behat && ./behat-build.sh ---rebuild=TRUE --site=vs-ae-uat-en'
	docker exec -t ${PROJECT_NAME}_appserver sh -c 'cd /behat && ./bin/behat --profile=vs-ae-uat-en-desktop --format cucumber_json --tags="@advanced-page"'
