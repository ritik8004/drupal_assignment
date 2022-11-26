#!make
include .env
export $(shell sed 's/=.*//' .env)

up:
	make start

start:
	@echo "Starting up containers"
	docker-compose pull
	docker-compose up -d --build --remove-orphans

pass:
	@echo Password
	@docker exec ${PROJECT_NAME} cat /var/jenkins_home/secrets/initialAdminPassword

stop:
	@echo "Stopping containers"
	@docker-compose stop

build:
	@echo "Building project"
	# @Todo, execute these commands in the appserver containner
	# Run composer and npm install
#	@docker exec appserver sh -c "cd /behat && composer update"
#	@docker exec appserver sh -c "cd /behat && npm install"
# Copy creds.json into the behat folder in the appserver
# @docker exec appserver sh -c "copy creds.json"

open:
	open "https://localhost:8080/"

in:
	make ssh

ssh:
	docker-compose exec ${PROJECT_NAME} bash
