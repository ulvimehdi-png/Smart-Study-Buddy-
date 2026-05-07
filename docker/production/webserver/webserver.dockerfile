# --- FRONTEND BUILD ---
FROM node:20-alpine AS frontend-build
WORKDIR /app/frontend
COPY frontend/ .

RUN npm ci \
    && npm run build

# --- BACKEND BUILD ---
FROM composer:2.9 AS backend-build
WORKDIR /app/backend

COPY backend/ .
RUN rm -rf .env* .gitignore tests var vendor phpunit.dist.xml README.md

COPY docker/production/prod-env/local-env .env
COPY docker/production/prod-env/local-env-prod .env.local

RUN composer validate --no-check-publish \
    && composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction \
    && php bin/console lint:yaml config \
    && php bin/console lint:container \
    && php bin/console cache:clear --env=prod

# --- FINAL IMAGE ---
FROM php:8.4-apache
WORKDIR /var/www/html

RUN apt-get update && apt-get install -y libicu-dev libzip-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=backend-build /app/backend /var/www/html
COPY --from=frontend-build /app/frontend/dist /var/www/html/public

COPY docker/production/webserver/apache/servername.conf /etc/apache2/conf-available/servername.conf
COPY docker/production/webserver/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

RUN a2enmod rewrite headers \
    && a2enconf servername

RUN chown -R www-data:www-data /var/www/html/var

COPY docker/production/webserver/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

CMD ["/entrypoint.sh"]