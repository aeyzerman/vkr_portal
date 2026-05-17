FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    libpng-dev libonig-dev libxml2-dev zip unzip \
    && docker-php-ext-install pdo pdo_mysql mbstring bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY docker/scripts/ /scripts/
RUN chmod +x /scripts/*.sh

WORKDIR /var/www

ENTRYPOINT ["/scripts/entrypoint.sh"]
