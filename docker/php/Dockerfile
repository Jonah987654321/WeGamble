# ==== Create PHP image ====
FROM php:8.3-fpm-alpine AS base
RUN apk add --no-cache \
    bash \
    mysql-client \
    $PHPIZE_DEPS
RUN docker-php-ext-install mysqli
WORKDIR /var/www/html


# ==== Composer deps builder ====
FROM composer:2 AS deps
WORKDIR /app
COPY src/composer.json src/composer.lock* ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress


# ==== Production image ====
FROM base AS prod
COPY src/ /var/www/html/
COPY --from=deps /app/vendor /var/www/html/vendor
RUN mkdir -p /var/www/logs && chown -R www-data:www-data /var/www/logs

# ==== Development image ====
FROM base AS dev
# We need composer for entrypoint-dev.sh (dependency installation)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY src/ /var/www/html/
RUN mkdir -p /var/www/logs && chown -R www-data:www-data /var/www/logs
