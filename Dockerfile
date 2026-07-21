FROM composer:2.8 AS composer
WORKDIR /var/www
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-dev --optimize-autoloader --no-scripts

FROM php:8.4-fpm
WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    curl libpng-dev libonig-dev libxml2-dev libpq-dev default-mysql-client zip unzip nginx \
    && docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY --from=composer /var/www/vendor /var/www/vendor

COPY . /var/www
COPY docker/nginx.conf.template /etc/nginx/conf.d/default.conf.template
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh \
    && mkdir -p /var/www/storage/framework/cache \
        /var/www/storage/framework/sessions \
        /var/www/storage/framework/views \
        /var/www/bootstrap/cache \
        /var/www/database \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap /var/www/database

ENV APP_ENV=production \
    APP_DEBUG=false \
    APP_URL=http://localhost \
    DB_CONNECTION=mysql \
    DB_HOST=mysql \
    DB_PORT=3306 \
    DB_DATABASE=ruang_tumbuh \
    DB_USERNAME=root \
    DB_PASSWORD=root

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
