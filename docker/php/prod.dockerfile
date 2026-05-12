# FRONTEND BUILD
FROM node:20-alpine AS frontend-build
WORKDIR /app/frontend

COPY frontend/ .

RUN npm ci && npm run build


# BACKEND BUILD
FROM composer:2.9 AS backend-build
WORKDIR /app/backend

COPY backend/ .

RUN rm -rf \
    .env* \
    .gitignore \
    tests \
    var \
    vendor \
    phpunit.dist.xml \
    README.md

COPY docker/prod-env/.env .env
COPY docker/prod-env/.env.prod .env.local

RUN composer validate --no-check-publish \
    && composer install \
        --no-dev \
        --prefer-dist \
        --optimize-autoloader \
        --no-interaction \
    && php bin/console lint:yaml config \
    && php bin/console lint:container \
    && php bin/console cache:clear --env=prod


# FINAL IMAGE
FROM php:8.4.18-apache
WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-install \
      intl \
      pdo \
      pdo_mysql \
      zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=backend-build /app/backend /var/www/html
COPY --from=frontend-build /app/frontend/dist /var/www/html/public

COPY docker/php/prod.ini \
    /usr/local/etc/php/conf.d/prod.ini

COPY docker/apache/servername.conf \
    /etc/apache2/conf-available/servername.conf

COPY docker/apache/000-default.conf \
    /etc/apache2/sites-available/000-default.conf

RUN a2enmod rewrite headers \
    && a2enconf servername

RUN chown -R www-data:www-data /var/www/html/var

COPY docker/scripts/prod-entrypoint.sh /docker/scripts/entrypoint.sh
COPY docker/scripts/wait-for-db.sh /docker/scripts/wait-for-db.sh

RUN chmod +x /docker/scripts/*.sh

CMD ["/docker/scripts/entrypoint.sh"]