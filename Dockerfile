# Step 1: Use the official PHP image with FPM (FastCGI Process Manager) for serving PHP
FROM php:8.2-fpm

# Step 2: Set the working directory inside the container
WORKDIR /var/www

# Step 3: Install dependencies for PHP extensions and other requirements
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev zip git && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install gd pdo pdo_mysql

# Step 4: Install Composer (PHP dependency manager)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Step 5: Copy the application files from the host machine into the container
COPY . .

# Step 6: Install PHP dependencies using Composer
RUN composer install --no-dev --optimize-autoloader

# Step 7: Expose the port that PHP-FPM will listen to
EXPOSE 9000

# Step 8: Command to run PHP-FPM when the container starts
CMD ["php-fpm"]
