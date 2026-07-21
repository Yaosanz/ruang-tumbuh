#!/bin/sh
set -eu

php artisan storage:link || true
php artisan migrate --force --seed
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

chown -R www-data:www-data /var/www/storage /var/www/bootstrap /var/www/database
service nginx start
exec php-fpm -F
