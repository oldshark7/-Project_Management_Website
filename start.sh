#!/bin/sh

touch database/database.sqlite

php artisan config:clear
php artisan cache:clear

php artisan migrate --force || true

php artisan optimize

php artisan serve --host=0.0.0.0 --port=$PORT