#!/bin/sh
set -e

# Render inject $PORT secara dinamis; fallback ke 10000 kalau tidak ada (lokal/testing)
export PORT="${PORT:-10000}"
envsubst '${PORT}' < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf

# Hapus default site bawaan Debian yang bisa bentrok (ini penyebab "Welcome to nginx!" sebelumnya)
rm -f /etc/nginx/sites-enabled/default

php artisan storage:link || true
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

php-fpm -D
nginx -g 'daemon off;'