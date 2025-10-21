FROM php:8.2-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip curl \
    libzip-dev libxml2-dev libicu-dev \
    && docker-php-ext-install pdo pdo_mysql soap zip intl \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN useradd -m -u 1000 appuser && chown -R appuser:appuser /var/www/html

COPY . .

RUN COMPOSER_MEMORY_LIMIT=-1 composer update --no-scripts --ignore-platform-req=php

RUN mkdir -p var && chmod -R 775 var && chown -R appuser:appuser var

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8000

HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD curl -f http://localhost:8000/health || exit 1

USER appuser

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
