#!/bin/bash

# Quick fix for Vite manifest error
# This script should be run from the project root directory

echo "=== Quick Fix for Vite Manifest Error ==="

# 1. Make sure we're in the project directory
cd /invoice

# 2. Install npm dependencies if node_modules doesn't exist
if [ ! -d "node_modules" ]; then
    echo "Installing npm dependencies..."
    npm install
fi

# 3. Create the SQLite database file if it doesn't exist
if [ ! -f "database/database.sqlite" ]; then
    echo "Creating SQLite database file..."
    touch database/database.sqlite
fi

# 4. Set up environment if .env doesn't exist
if [ ! -f ".env" ]; then
    echo "Setting up environment file..."
    cp .env.example .env
    php artisan key:generate

    # Configure for SQLite
    sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/' .env
    sed -i 's/DB_HOST=127.0.0.1/#DB_HOST=127.0.0.1/' .env
    sed -i 's/DB_PORT=3306/#DB_PORT=3306/' .env
    sed -i 's/DB_DATABASE=laravel/DB_DATABASE=\/invoice\/database\/database.sqlite/' .env
    sed -i 's/DB_USERNAME=root/#DB_USERNAME=root/' .env
    sed -i 's/DB_PASSWORD=/#DB_PASSWORD=/' .env
fi

# 5. Build assets with Vite
echo "Building assets with Vite..."
npm run build

# 6. Run migrations
echo "Running database migrations..."
php artisan migrate --force

# 7. Create storage symlink
echo "Creating storage symlink..."
php artisan storage:link

# 8. Clear and cache configurations
echo "Clearing and caching configurations..."
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 9. Set permissions
echo "Setting proper permissions..."
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache

echo "=== Quick fix completed! ==="
echo "You should now be able to access your application."
echo "If running in development, use: php artisan serve"
