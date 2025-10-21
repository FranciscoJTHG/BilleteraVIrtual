#!/bin/bash
set -e

echo "Starting SOAP Service..."

if [ "$APP_ENV" = "dev" ] || [ "$APP_ENV" = "test" ]; then
    echo "Running database migrations..."
    php bin/console doctrine:migrations:migrate --no-interaction
fi

echo "SOAP Service ready!"
exec php -S 0.0.0.0:8000 -t public
