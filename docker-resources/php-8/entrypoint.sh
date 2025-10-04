#!/bin/sh
set -e
if [ -f "/var/www/html/composer.json" ]; then 
echo "Installing PHP dependencies via Composer..." 
composer install --no-interaction --optimize-autoloader 
fi


if [ -f "/var/www/html/artisan" ]; then
    echo "Running migrations..."
    php artisan migrate --force
fi

exec "$@"
