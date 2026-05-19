#!/bin/sh
set -eux

apt-get update

apt-get install -y --no-install-recommends \
          libicu-dev \
          libzip-dev \
          libxml2-dev \
          libxslt1-dev \
          libpng-dev \
          libjpeg62-turbo-dev \
          libfreetype6-dev \
          libonig-dev

docker-php-ext-configure gd --with-freetype --with-jpeg

docker-php-ext-install -j"$(nproc)" \
        exif ftp gd gettext intl mbstring mysqli \
        opcache pdo_mysql shmop sockets sysvmsg sysvsem sysvshm \
        xsl zip

rm -rf /var/lib/apt/lists/*