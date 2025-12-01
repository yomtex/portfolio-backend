FROM php:8.2-fpm

# ------------------------------
# 1. Install system dependencies
# ------------------------------
RUN apt-get update && apt-get install -y \
    build-essential \
    curl \
    zip \
    unzip \
    nginx \
    sqlite3 \
    libsqlite3-dev \
    supervisor \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# ------------------------------
# 2. Install PHP extensions
# ------------------------------
RUN docker-php-ext-install pdo pdo_mysql pdo_sqlite

# ------------------------------
# 3. Install Composer
# ------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ------------------------------
# 4. Set working directory
# ------------------------------
WORKDIR /var/www/html

# ------------------------------
# 5. Copy project files
# ------------------------------
COPY . .

# ------------------------------
# 6. Ensure SQLite database exists & proper permissions
# ------------------------------
RUN touch database/database.sqlite \
    && chown -R www-data:www-data database storage bootstrap/cache \
    && chmod -R 775 database storage bootstrap/cache

# ------------------------------
# 7. Install PHP dependencies
# ------------------------------
RUN composer install --no-dev --optimize-autoloader

# ------------------------------
# 8. Copy nginx config
# ------------------------------
COPY ./deploy/nginx.conf /etc/nginx/sites-available/default

# ------------------------------
# 9. Set environment port
# ------------------------------
ENV PORT=10000
EXPOSE 10000

# ------------------------------
# 10. Supervisor config
# ------------------------------
COPY ./deploy/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
# Create the storage link
RUN php artisan storage:link
# ------------------------------
# 11. Start migrations/seeders + supervisor at runtime
# ------------------------------
CMD php artisan migrate --force --seed && supervisord -n
