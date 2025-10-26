FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer.json /var/www/html/composer.json

COPY _docker/app/php.ini /usr/local/etc/php/php.ini

WORKDIR /var/www/html

RUN composer install --no-scripts --no-autoloader --no-dev --prefer-dist