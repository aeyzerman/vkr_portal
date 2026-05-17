FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    libpng-dev libonig-dev libxml2-dev zip unzip curl \
    && docker-php-ext-install pdo pdo_mysql mbstring bcmath \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY docker/scripts/ /scripts/
RUN chmod +x /scripts/*.sh

WORKDIR /var/www

ENTRYPOINT ["/scripts/entrypoint.sh"]
