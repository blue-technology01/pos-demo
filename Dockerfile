FROM php:8.4-fpm as php

RUN usermod --uid 1000 www-data && groupmod --gid 1000 www-data

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    unzip \
    git \
    mariadb-client \
    libmariadb-dev \
    libssl-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libicu-dev \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    build-essential \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        intl \
        zip \
        bcmath \
        soap \
        mysqli \
        pdo_mysql \
    && apt-get autoremove -y && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Copy composer binary
COPY --from=composer:2.3.5 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files first for better layer caching
COPY --chown=www-data:www-data src/composer.json src/composer.lock ./

# Install PHP dependencies
# RUN composer install

# Copy Laravel code
COPY --chown=www-data:www-data src/ .

# Run composer scripts (if needed)
RUN composer dump-autoload --optimize

# Create storage folders & bootstrap cache
RUN mkdir -p storage/framework/{cache,testing,sessions,views} \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 755 storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]