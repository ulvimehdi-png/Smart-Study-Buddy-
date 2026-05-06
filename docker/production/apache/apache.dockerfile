FROM php:8.4.18-apache

RUN apt-get update && apt-get install -y \
    git unzip zip curl libicu-dev libzip-dev libonig-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite headers

COPY ./000-default.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html
