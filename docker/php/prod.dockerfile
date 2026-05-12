##
# FRONTEND BUILD
##
FROM node:20-alpine AS frontend-build
WORKDIR /app/frontend

COPY frontend/package*.json ./

RUN npm ci

COPY frontend/ .

RUN npm run build

##
# BACKEND BUILD
##
FROM composer:2.9 AS backend-build

WORKDIR /app/backend

COPY backend/composer.json backend/composer.lock ./

RUN composer validate --no-check-publish

RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-scripts

COPY backend/ .
RUN rm -f .env*

COPY ./docker/environment/prod/prod.php.env .env

RUN composer dump-autoload --no-dev --optimize

RUN php bin/console lint:yaml config \
    && php bin/console lint:container

RUN composer clear-cache \
    && php bin/console cache:clear --env=prod

##
# FINAL IMAGE
##
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

COPY ./docker/php/prod.ini /usr/local/etc/php/conf.d/prod.ini

COPY ./docker/apache/servername.conf /etc/apache2/conf-available/servername.conf

COPY ./docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

RUN a2enmod rewrite headers
RUN a2enconf servername

RUN chown -R www-data:www-data /var/www/html/var

COPY ./docker/scripts/prod-entrypoint.sh /scripts/entrypoint.sh

COPY ./docker/scripts/wait-for-db.sh /scripts/wait-for-db.sh

RUN chmod +x /scripts/*.sh

CMD ["/scripts/entrypoint.sh"]