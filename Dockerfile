# ==============================================================================
# Stage 1: Frontend Asset Builder (Node.js & Vite)
# ==============================================================================
FROM node:22-alpine AS frontend-builder

WORKDIR /app

# Install npm dependencies
COPY package.json package-lock.json ./
RUN npm ci --prefer-offline --no-audit

# Copy source assets & build configs
COPY resources resources/
COPY public public/
COPY vite.config.js ./

# Compile production assets via Vite
RUN npm run build

# ==============================================================================
# Stage 2: Composer Dependency Builder (PHP Vendor)
# ==============================================================================
FROM composer:2 AS vendor-builder

WORKDIR /app

# Install PHP dependencies without scripts (prevents early artisan calls)
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --no-scripts \
    --no-autoloader \
    --ignore-platform-reqs

# Copy application source code
COPY . .

# Generate optimized authoritative classmap
RUN composer dump-autoload \
    --optimize \
    --no-dev \
    --classmap-authoritative

# ==============================================================================
# Stage 3: Production PHP-FPM Runtime Image
# ==============================================================================
FROM php:8.3-fpm-alpine AS runner

LABEL maintainer="Zonagim Development Team"
LABEL description="Production PHP 8.3 FPM image for Zonagim Marketplace"

# Install php-extension-installer helper
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# Install essential PHP extensions for Laravel 12 & Filament 5
RUN install-php-extensions \
    bcmath \
    exif \
    gd \
    intl \
    mbstring \
    opcache \
    pcntl \
    pdo_mysql \
    redis \
    zip

# Install runtime utilities & timezone data
RUN apk add --no-cache \
    bash \
    curl \
    tzdata \
    && cp /usr/share/zoneinfo/Asia/Jakarta /etc/localtime \
    && echo "Asia/Jakarta" > /etc/timezone

# Copy custom PHP & OPcache configs
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Set application directory
WORKDIR /var/www

# Copy codebase from vendor-builder
COPY --from=vendor-builder --chown=www-data:www-data /app /var/www

# Copy built frontend assets from frontend-builder
COPY --from=frontend-builder --chown=www-data:www-data /app/public/build /var/www/public/build

# Copy and setup entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Create and set permissions for storage and bootstrap cache
RUN mkdir -p \
    /var/www/storage/framework/cache/data \
    /var/www/storage/framework/sessions \
    /var/www/storage/framework/views \
    /var/www/storage/logs \
    /var/www/storage/app/public \
    /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

CMD ["php-fpm"]
