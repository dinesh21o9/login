# Use an official PHP 8.2.4 image with Apache
FROM php:8.2.4-apache

# Set the working directory in the container
WORKDIR /var/www/html

# Copy the application code into the container
COPY . /var/www/html

# Install PHP extensions and Composer
RUN apt-get update && \
    apt-get install -y libpq-dev && \
    docker-php-ext-install pdo pdo_mysql pdo_pgsql && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Composer dependencies
RUN composer install

# Enable Apache modules and configure .htaccess (if needed)
RUN a2enmod rewrite
# COPY .htaccess /var/www/html/.htaccess

# Expose port 80 for Apache
EXPOSE 80

# Start the Apache web server
CMD ["apache2-foreground"]
