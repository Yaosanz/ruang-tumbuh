#!/bin/sh

set -e

export PORT=${PORT:-10000}

echo "Generating nginx config..."

envsubst '${PORT}' \
< /etc/nginx/conf.d/default.conf.template \
> /etc/nginx/conf.d/default.conf

rm -f /etc/nginx/sites-enabled/default

cd /var/www

echo "Creating storage link..."

php artisan storage:link || true

echo "Running migrations..."

php artisan migrate --force

SEED_CHECK=$(php artisan tinker --execute="echo App\Models\Quiz::count();" 2>/dev/null || echo "0")

if [ "$SEED_CHECK" = "0" ]; then

    echo "Running Seeder..."

    php artisan db:seed --force

else

    echo "Database already seeded."

fi

echo "Clearing caches..."

php artisan optimize:clear

echo "Caching..."

php artisan config:cache

php artisan view:cache

echo ""
echo "===== LIVEWIRE ====="

php artisan route:list | grep livewire || true

echo ""
echo "===== ABOUT ====="

php artisan about

echo ""
echo "Starting PHP-FPM..."

php-fpm -D

echo "Starting Nginx..."

exec nginx -g "daemon off;"