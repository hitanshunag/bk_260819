#!/usr/bin/env bash

red=$'\e[1;31m'
grn=$'\e[1;32m'
blu=$'\e[1;34m'
mag=$'\e[1;35m'
cyn=$'\e[1;36m'
white=$'\e[0m'

sudo apt update
sudo apt install -y curl

echo " $red ----- Initializing Docker Files ------- $white "
docker-compose down && docker-compose up --build -d

echo " $grn -------Installing Project Dependencies -----------$blu "
sudo sleep 300s #this line is included for composer to finish the dependency installation so that test case can execute without error.

echo " $blu ----- Running Migrations & Data Seeding ------- $white "
sudo chmod 777 -R ./codebase/*

docker exec rest_order_app_php php artisan migrate
docker exec rest_order_app_php php artisan db:seed

echo " $blu ----- Running Feature test cases ------- $white "
docker exec rest_order_app_php php ./vendor/phpunit/phpunit/phpunit /var/www/html/tests/Feature/OrderFeatureTest.php

echo " $blu ----- Running Unit test cases ------- $white "
docker exec rest_order_app_php php ./vendor/phpunit/phpunit/phpunit /var/www/html/tests/Unit

exit 0