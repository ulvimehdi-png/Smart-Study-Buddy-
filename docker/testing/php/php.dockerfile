# --- BACKEND BUILD ---
FROM php:8.4.18-cli AS backend-build
WORKDIR /app/backend

ENV APP_ENV=test
ENV APP_DEBUG=0
ENV DATABASE_URL="mysql://app_test:app_test@database:3306/app?serverVersion=8.0.32&charset=utf8mb4"

RUN apt-get update && apt-get install -y \
    git unzip zip curl libicu-dev libzip-dev libpng-dev libonig-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2.9.5 /usr/bin/composer /usr/bin/composer

COPY backend/ ./

RUN rm -rf var vendor

RUN composer install --prefer-dist --no-interaction

RUN php bin/console lint:yaml config \
    && php bin/console lint:container \
    && php bin/console cache:clear --env=test

COPY docker/testing/php/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

CMD ["/entrypoint.sh"]