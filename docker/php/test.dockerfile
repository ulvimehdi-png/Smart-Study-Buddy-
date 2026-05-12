FROM php:8.4.18-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    && docker-php-ext-install \
      intl \
      pdo \
      pdo_mysql \
      zip \
    && rm -rf /var/lib/apt/lists/*

# COMPOSER
COPY --from=composer:2.9 /usr/bin/composer /usr/bin/composer

WORKDIR /app/backend