#!/bin/sh
set -e

# Render (dan platform sejenis) inject $PORT secara dinamis saat runtime.
# Fallback ke 10000 untuk kebutuhan lokal/testing.
export PORT="${PORT:-10000}"

echo "Generating nginx config for port ${PORT}..."
envsubst '${PORT}' < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf

# Pastikan tidak ada sisa default site yang bentrok
rm -f /etc/nginx/sites-enabled/default

cd /var/www

php artisan storage:link || true

echo "Running migrations..."
php artisan migrate --force

# Seed hanya dijalankan sekali secara aman: cek dulu apakah tabel quizzes sudah berisi data.
# Kalau mau selalu re-seed di tiap deploy (misal untuk demo), hapus blok pengecekan ini.
SEED_CHECK=$(php artisan tinker --execute="echo App\Models\Quiz::count();" 2>/dev/null || echo "0")
if [ "$SEED_CHECK" = "0" ]; then
    echo "Seeding database..."
    php artisan db:seed --force
else
    echo "Database already seeded, skipping."
fi

echo "Caching config..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting php-fpm..."
php-fpm -D

echo "Starting nginx on port ${PORT}..."
exec nginx -g 'daemon off;'