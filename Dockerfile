# Stage 1: Build frontend assets
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: PHP-FPM & Nginx on Alpine
FROM php:8.2-fpm-alpine

# Install system dependencies and runtime libraries
RUN apk add --no-cache \
    nginx \
    bash \
    curl \
    git \
    libpng \
    libjpeg-turbo \
    freetype \
    libzip \
    libpq \
    oniguruma

# Install build dependencies, configure and install PHP extensions
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    postgresql-dev \
    oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        pdo_pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
    && apk del .build-deps

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Copy compiled frontend assets from frontend stage
COPY --from=frontend /app/public/build /var/www/html/public/build

# Copy Nginx configuration and entrypoint script
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/start.sh /usr/local/bin/start.sh

# Ensure script is executable and convert any Windows CRLF to LF
RUN sed -i 's/\r$//' /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

# Run composer install optimized for production
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set correct permissions for storage and bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose Render standard port and HTTP
EXPOSE 80 10000

# Start services via start.sh
CMD ["/usr/local/bin/start.sh"]
