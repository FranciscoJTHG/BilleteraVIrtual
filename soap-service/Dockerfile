FROM php:8.2-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip \
    libzip-dev libxml2-dev libicu-dev \
    && docker-php-ext-install pdo pdo_mysql soap zip intl \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install

EXPOSE 8000

HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD php bin/console doctrine:query:sql "SELECT 1" || exit 1

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
