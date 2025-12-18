#!/bin/bash
set -e
exec > >(tee -a /var/www/html/storage/logs/entrypoint.log) 2>&1

# Clear config cache securely
php artisan config:clear || true

# Only run setup tasks if the command is php-fpm (main app container)
if [ "$1" = 'php-fpm' ]; then
    echo "Running deployment tasks..."
    
    echo "Installing dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader

    echo "Running migrations..."
    php artisan migrate --force
    php artisan migrate --force --path=database/migrations/enquetes_de_gouvernance
    
    # Create directory for permissions command to avoid "No such file" error
    echo "Creating permissions directory..."
    mkdir -p /var/www/html/help/permissions
    chown -R www-data:www-data /var/www/html/help

    # Fix storage permissions BEFORE seeding to ensure logging works
    echo "Fixing storage permissions (early)..."
    mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
    
    # Execute permissions command
    echo "Executing permissions command..."
    php artisan permissions

    echo "Seeding database..."
    # Allow seeding to fail without crashing deployment (for now)
    php artisan db:seed --force || echo "Seeding failed, but continuing deployment..."

    # Passport configuration removed as package is not installed
    # echo "Configuring Passport..."
    # php artisan passport:keys
    # Create client only if it doesn't exist
    # php artisan passport:client --personal --name="Personal Access Client" --no-interaction || true

    echo "Linking storage..."
    php artisan storage:link
fi

# Ensure permissions are correct for all services (app, worker, scheduler) again
# just in case something changed or for non-php-fpm commands
echo "Ensuring storage permissions..."
mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

exec "$@"
