ARG PHP_VERSION=8.4.18
ARG NODE_VERSION=22-alpine
ARG COMPOSER_VERSION=2.9
ARG MYSQL_VERSION=8.0.32
ARG MERCURE_VERSION=v0.24.0

#################
# COMPOSER BASE #
#################
FROM composer:${COMPOSER_VERSION} AS composer-bin

################
# PHP BASE CLI #
################
FROM php:${PHP_VERSION}-cli AS php-base

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        curl \
        libicu-dev \
        libzip-dev \
        default-mysql-client \
    && docker-php-ext-install -j"$(nproc)" \
        intl \
        pdo \
        pdo_mysql \
        zip \
        opcache \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer-bin /usr/bin/composer /usr/bin/composer

WORKDIR /app/backend

###############
# DEV TARGET  #
###############
FROM php-base AS php-dev

ENV APP_ENV=dev \
    COMPOSER_HOME=/tmp/composer \
    COMPOSER_CACHE_DIR=/app/.composer-cache

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# SYMFONY CLI
COPY --link --from=ghcr.io/symfony-cli/symfony-cli:5.17.1 \
    /usr/local/bin/symfony /usr/local/bin/symfony

EXPOSE 8000

###############
# TEST TARGET #
###############
FROM php-base AS php-test

ENV APP_ENV=test \
    COMPOSER_HOME=/tmp/composer \
    COMPOSER_CACHE_DIR=/app/.composer-cache

###############
# FRONTEND    #
###############
FROM node:${NODE_VERSION} AS frontend-build

WORKDIR /app/frontend

COPY frontend/package*.json ./
RUN npm ci

COPY frontend/public ./public
COPY frontend/src ./src
COPY frontend/index.html ./
COPY frontend/*.js* ./

RUN npm run build

###############
# VENDOR      #
###############
FROM php-base AS backend-vendor

ENV APP_ENV=prod \
    APP_DEBUG=0 \
    COMPOSER_HOME=/tmp/composer \
    COMPOSER_CACHE_DIR=/tmp/composer-cache

WORKDIR /app/backend

# Rather avoid this but symfony complains without and env-file and it's just prod-emulation
COPY docker/environment/.env.local.prod ./.env

COPY backend/composer.json ./
COPY backend/composer.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --classmap-authoritative \
    --no-interaction \
    --no-scripts

COPY backend/bin ./bin
COPY backend/config ./config
COPY backend/migrations ./migrations
COPY backend/public ./public
COPY backend/src ./src
COPY backend/.htaccess ./.htaccess

RUN composer dump-autoload \
    --no-dev \
    --optimize \
    --classmap-authoritative

RUN php bin/console lint:yaml config \
    && php bin/console lint:container

##################
# PROD EMULATION #
##################
FROM php:${PHP_VERSION}-apache AS php-prod

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        libicu-dev \
        libzip-dev \
        default-mysql-client \
    && docker-php-ext-install -j"$(nproc)" \
        intl \
        pdo \
        pdo_mysql \
        zip \
        opcache \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/apache/servername.conf /etc/apache2/conf-available/servername.conf
COPY docker/php/prod.ini /usr/local/etc/php/conf.d/zz-prod.ini

RUN a2enmod rewrite headers
RUN a2enconf servername

COPY --from=backend-vendor --chown=www-data:www-data /app/backend/ /var/www/html/
COPY --from=frontend-build --chown=www-data:www-data /app/frontend/dist/ /var/www/html/public/

RUN mkdir -p var/cache var/log public/upload \
    && chown -R www-data:www-data var public/upload

EXPOSE 80

HEALTHCHECK --interval=10s --timeout=3s --retries=10 \
  CMD curl -fsS http://127.0.0.1/ || exit 1