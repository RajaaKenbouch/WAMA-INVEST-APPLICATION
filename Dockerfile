FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" gd mbstring pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite \
    && { \
        echo "upload_max_filesize=32M"; \
        echo "post_max_size=40M"; \
        echo "memory_limit=512M"; \
        echo "max_execution_time=120"; \
        echo "date.timezone=Africa/Casablanca"; \
    } > /usr/local/etc/php/conf.d/app.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-progress

COPY . .

RUN mkdir -p uploads/originals uploads/pdfs \
    && chown -R www-data:www-data uploads
