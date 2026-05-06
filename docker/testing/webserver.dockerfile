# --- FRONTEND BUILD ---
FROM node:20-alpine AS frontend-build
WORKDIR /app/frontend
COPY frontend/ .
RUN npm ci
RUN npm run build

# --- BACKEND BUILD ---
FROM composer:2.9 AS backend-build
WORKDIR /app/backend
COPY backend/ .
RUN composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader

RUN php bin/console cache:clear --env=prod

# --- FINAL IMAGE ---
FROM php:8.4-apache

RUN apt-get update && apt-get install -y \
    libicu-dev libzip-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=backend-build /app/backend /var/www/html
COPY --from=frontend-build /app/frontend/dist /var/www/html/public

RUN a2enmod rewrite headers

COPY docker/apache/servername.conf /etc/apache2/conf-available/servername.conf
RUN a2enconf servername

COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

RUN chown -R www-data:www-data /var/www/html/var

CMD ["apache2-foreground"]