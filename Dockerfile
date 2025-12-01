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

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set Laravel permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# Copy nginx config
COPY ./deploy/nginx.conf /etc/nginx/sites-available/default

# Expose port (Render sets $PORT, usually 10000)
ENV PORT=10000
EXPOSE 10000

# Start PHP-FPM and nginx
CMD php-fpm & nginx -g 'daemon off;'
