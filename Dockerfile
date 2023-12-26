# Use a more up-to-date base image
FROM php:8.2-fpm-alpine

# Update package lists and install necessary tools
RUN apk update && apk upgrade && \
    apk add --no-cache zip unzip libzip-dev

# Install required PHP extensions
RUN docker-php-ext-install zip pdo pdo_mysql

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/html

# Copy Laravel project files
COPY . .

# Install project dependencies
RUN composer update
RUN composer install --no-dev



# Generate Laravel application key
RUN php artisan key:generate

# Expose port 9000 (used by PHP-FPM)
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
