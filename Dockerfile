FROM php:8.3-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    libavif-dev \
    libwebp-dev \
    libjpeg-dev \
    libfreetype6-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions with AVIF support
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-avif && \
    docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Configure git for the www-data user
RUN git config --system --add safe.directory /var/www/html

# Copy application files
COPY . .

# Set permissions before running composer
RUN chown -R www-data:www-data /var/www/html

# Switch to www-data user for composer and npm operations
USER www-data

# Install dependencies
RUN composer install --no-interaction --no-dev --prefer-dist
RUN npm install

# Switch back to root for Apache configuration
USER root

# Configure Apache
RUN a2enmod rewrite

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"] 