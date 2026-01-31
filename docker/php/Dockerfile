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

WORKDIR /var/www/vt-tracker-api

# Copy composer files first to leverage Docker cache
COPY composer.json composer.lock ./

# Install dependencies (no scripts yet, just libs)
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copy the rest of the application
COPY . .

# Finish composer setup
RUN composer dump-autoload --optimize && \
    chmod -R 775 storage bootstrap/cache