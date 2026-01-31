FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    zip \
    intl \
    && rm -rf /var/lib/apt/lists/*

# Install Composer (latest stable)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html