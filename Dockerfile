# syntax=docker/dockerfile:1

# ===== Stage 1: Build frontend assets (Vite) =====
FROM node:20-alpine AS assets
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# ===== Stage 2: Install PHP dependencies (Composer) =====
FROM composer:2 AS composer
WORKDIR /app
COPY . .
# --no-scripts penting: mencegah artisan package:discover berjalan di sini.
# Stage ini belum punya bootstrap/cache yang writable dengan benar, jadi
# artisan akan gagal ("Please provide a valid cache path"). Package
# discovery akan otomatis ter-generate ulang secara natural saat
# entrypoint.sh menjalankan artisan command pertama kali di runtime.
RUN composer install --no-dev --no-scripts --optimize-autoloader --no-interaction

# ===== Stage 3: Final runtime image (PHP-FPM + Nginx) =====
FROM php:8.3-fpm AS final

# System packages: build deps untuk ekstensi PHP + nginx + envsubst (gettext-base)
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    nginx \
    gettext-base \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www

# Copy source code aplikasi
COPY . .

# Copy vendor (dependency PHP) dari stage composer
COPY --from=composer /app/vendor ./vendor

# Copy hasil build asset Vite dari stage assets
COPY --from=assets /app/public/build ./public/build

# Hapus default site nginx bawaan Debian — mencegah halaman
# "Welcome to nginx!" muncul karena bentrok dengan config custom
RUN rm -f /etc/nginx/sites-enabled/default /etc/nginx/conf.d/default.conf

# Copy template config nginx — file asli di-generate saat container
# start (entrypoint.sh) karena $PORT baru tersedia saat runtime, bukan build time
COPY docker/nginx.conf.template /etc/nginx/conf.d/default.conf.template

# Copy entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Siapkan folder yang wajib writable oleh Laravel
RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 10000

ENTRYPOINT ["entrypoint.sh"]