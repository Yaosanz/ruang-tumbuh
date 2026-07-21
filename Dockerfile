# syntax=docker/dockerfile:1

############################
# Stage 1 - Build Vite
############################
FROM node:20-alpine AS assets

WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .

RUN npm run build


############################
# Stage 2 - PHP + Nginx
############################
FROM php:8.4-fpm

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
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

COPY --from=assets /app/public/build ./public/build

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-scripts

RUN rm -f \
    /etc/nginx/sites-enabled/default \
    /etc/nginx/conf.d/default.conf

COPY docker/nginx.conf.template /etc/nginx/conf.d/default.conf.template

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh

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