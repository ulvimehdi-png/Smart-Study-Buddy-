ARG PHP_VERSION=8.4.18
ARG NODE_VERSION=22-alpine
ARG COMPOSER_VERSION=2.9.5
ARG MYSQL_VERSION=8.0.32
ARG MERCURE_VERSION=v0.24.0

#################
# COMPOSER BASE #
#################
FROM composer:${COMPOSER_VERSION} AS composer-bin

############
# PHP BASE #
############
FROM php:${PHP_VERSION}-apache AS php-base

WORKDIR /app/backend

# PHP extensions
COPY docker/php/install-extensions.sh /tmp/install-extensions.sh

RUN chmod +x /tmp/install-extensions.sh \
    && sh /tmp/install-extensions.sh \
    && rm /tmp/install-extensions.sh

# Base PHP config
COPY docker/php/base.ini /usr/local/etc/php/conf.d/00-base.ini

# Composer
COPY --from=composer-bin /usr/bin/composer /usr/bin/composer

# Symfony CLI
COPY --link --from=ghcr.io/symfony-cli/symfony-cli:5.17.1 \
    /usr/local/bin/symfony /usr/local/bin/symfony

###############
# DEV TARGET  #
###############
FROM php-base AS php-dev

WORKDIR /app/backend

ENV APP_ENV=dev

COPY docker/php/dev.ini /usr/local/etc/php/conf.d/99-dev.ini

# Xdebug ONLY in dev
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && rm -rf /tmp/pear

EXPOSE 8000

###############
# TEST TARGET #
###############
FROM php-base AS php-test

WORKDIR /app/backend

# Xdebug ONLY in dev
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && rm -rf /tmp/pear

ENV APP_ENV=test
RUN echo "APP_ENV=test" > /app/backend/.env

COPY docker/php/test.ini /usr/local/etc/php/conf.d/99-test.ini

COPY backend/ .

RUN composer install \
      --prefer-dist \
      --no-interaction \
      --optimize-autoloader \
      --classmap-authoritative \
      --no-scripts

RUN rm -rf var/coverage \
      && mkdir -p var/coverage

#######################
# TEST COVERAGE IMAGE #
#######################
FROM php-test AS php-test-coverage

WORKDIR /app/backend

ENV APP_ENV=test
ENV XDEBUG_MODE=coverage



##########
# VENDOR #
##########
FROM php-base AS backend-vendor

WORKDIR /app/backend

ENV APP_ENV=prod

COPY docker/php/prod.ini /usr/local/etc/php/conf.d/99-prod.ini

COPY backend/ .

RUN composer install \
      --no-dev \
      --prefer-dist \
      --no-interaction \
      --optimize-autoloader \
      --classmap-authoritative \
      --no-scripts

############
# FRONTEND #
############
FROM node:${NODE_VERSION} AS frontend-build

WORKDIR /app/frontend

COPY frontend/package*.json ./

RUN npm ci

COPY frontend/ .

RUN npm run build

##################
# PROD EMULATION #
##################
FROM php-base AS php-prod

WORKDIR /var/www/html

ENV APP_ENV=prod

COPY docker/php/prod.ini /usr/local/etc/php/conf.d/99-prod.ini

COPY docker/apache/000-default.conf \
    /etc/apache2/sites-available/000-default.conf

COPY docker/apache/servername.conf \
    /etc/apache2/conf-available/servername.conf

RUN a2enmod rewrite headers \
    && a2enconf servername

# Backend
COPY --from=backend-vendor --chown=www-data:www-data /app/backend/ /var/www/html/
COPY --from=frontend-build --chown=www-data:www-data /app/frontend/dist/ /var/www/html/public/

RUN mkdir -p var/cache var/log public/upload \
    && chown -R www-data:www-data var public/upload

EXPOSE 80

HEALTHCHECK --interval=10s --timeout=3s --retries=10 \
    CMD curl -fsS http://127.0.0.1/ || exit 1