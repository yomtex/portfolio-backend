FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    curl \
    zip \
    unzip \
    nginx \
    sqlite3 \
    libsqlite3-dev \
    supervisor

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql pdo_sqlite

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy project files
WORKDIR /var/www/html
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Laravel permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# Copy nginx config
COPY ./deploy/nginx.conf /etc/nginx/nginx.conf

# Use $PORT
ENV PORT=10000

# Expose port (optional, just for documentation)
EXPOSE 10000

# Start PHP-FPM and nginx in foreground
CMD sh -c "php-fpm && nginx -g 'daemon off;'"
