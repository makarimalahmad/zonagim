#!/bin/sh
set -e

echo "==> [Zonagim] Initializing container..."

# 1. Wait for database connection if DB_HOST is set
if [ -n "$DB_HOST" ]; then
    echo "==> [Zonagim] Waiting for MySQL database ($DB_HOST)..."
    MAX_TRIES=30
    COUNT=0
    until php -r "try { new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '3306') . ';dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'), [PDO::ATTR_TIMEOUT => 2]); exit(0); } catch (Exception \$e) { exit(1); }" > /dev/null 2>&1; do
        COUNT=$((COUNT + 1))
        if [ $COUNT -ge $MAX_TRIES ]; then
            echo "==> [Zonagim] ERROR: Database connection timed out after $MAX_TRIES attempts."
            break
        fi
        echo "==> [Zonagim] Database not ready yet ($COUNT/$MAX_TRIES). Waiting..."
        sleep 2
    done
    echo "==> [Zonagim] Database connection established!"
fi

# 2. Ensure storage symlink exists
echo "==> [Zonagim] Ensuring storage symlink..."
php artisan storage:link --force || true

# 3. Run migrations automatically if enabled (default: true)
if [ "${AUTO_MIGRATE:-true}" = "true" ]; then
    echo "==> [Zonagim] Running database migrations..."
    php artisan migrate --force
fi

# 4. Warm up production caches if APP_ENV=production
if [ "${APP_ENV:-production}" = "production" ]; then
    echo "==> [Zonagim] Warming up production caches..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan filament:optimize || true
fi

# 5. Fix permissions for storage and bootstrap/cache
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

echo "==> [Zonagim] Initialization complete. Starting service..."

# Execute passed command (e.g., php-fpm or artisan queue:work)
exec "$@"
