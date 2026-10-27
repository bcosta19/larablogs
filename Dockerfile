FROM node:24-bookworm-slim AS node
FROM composer:2 AS composer

FROM php:8.4-apache-bookworm AS base
RUN apt-get update \
    && apt-get install -y --no-install-recommends libsqlite3-dev libonig-dev unzip ca-certificates \
    && docker-php-ext-install -j2 pdo_sqlite mbstring pcntl opcache \
    && a2enmod rewrite \
    && sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf \
    && rm -rf /var/lib/apt/lists/*
COPY deploy/apache.conf /etc/apache2/sites-available/000-default.conf
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
WORKDIR /var/www/html

FROM base AS build
COPY --from=composer /usr/bin/composer /usr/local/bin/composer
COPY --from=node /usr/local/bin/node /usr/local/bin/node
COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx
COPY . .
ENV APP_ENV=production APP_DEBUG=false VITE_APP_NAME=Larablogs
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader \
    && npm ci \
    && npm run build \
    && rm -rf node_modules

FROM base AS runtime
RUN groupmod -g 1000 www-data && usermod -u 1000 -g 1000 www-data \
    && mkdir -p /var/run/apache2 /var/lock/apache2 /data \
    && chown -R 1000:1000 /var/run/apache2 /var/lock/apache2 /var/log/apache2 /data
COPY --chown=1000:1000 --from=build /var/www/html /var/www/html
COPY --chmod=755 deploy/entrypoint.sh /usr/local/bin/larablogs-entrypoint
USER www-data
EXPOSE 8080
ENTRYPOINT ["larablogs-entrypoint"]
CMD ["apache2-foreground"]
