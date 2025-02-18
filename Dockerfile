# Use an official PHP 8.2.4 image as the base image
FROM php:8.2.4-apache

# Set the working directory in the container
WORKDIR /var/www/html

# Install Git
RUN apt-get update && \
    apt-get install -y git

# Copy only the composer files first to leverage Docker cache
COPY composer.json composer.lock /var/www/html/
COPY .env /var/www/html/.env

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install PHP extensions and Composer dependencies
RUN apt-get install -y libpq-dev && \
    docker-php-ext-install pdo pdo_mysql pdo_pgsql mysqli && \
    composer install

# Enable Apache modules and configure .htaccess (if needed)
RUN a2enmod rewrite

# Copy the rest of the application
COPY . /var/www/html

# Expose port 80 for Apache
EXPOSE 80

# Start the Apache web server
CMD ["apache2-foreground"]
