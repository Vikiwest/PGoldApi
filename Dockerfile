FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd pdo_sqlite

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Create storage directories
RUN mkdir -p storage/logs \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p storage/framework/cache \
    && mkdir -p storage/api-docs

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 storage \
    && chmod -R 755 bootstrap/cache \
    && chmod -R 777 storage/logs \
    && chmod -R 777 storage/framework

# Create .env file from example
RUN cp .env.example .env

# Create SQLite database
RUN touch database/database.sqlite \
    && chmod 666 database/database.sqlite

# Install dependencies
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Generate key and cache
RUN php artisan key:generate --force \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Create startup script
RUN echo '#!/bin/bash\n\
echo "Generating Swagger documentation..."\n\
php artisan l5-swagger:generate\n\
echo "Starting Apache..."\n\
apache2-foreground' > /start.sh && chmod +x /start.sh

# Apache configuration
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80


RUN echo '#!/bin/bash\n\
echo "🔧 Running startup tasks..."\n\
echo "📚 Generating Swagger documentation..."\n\
php artisan l5-swagger:generate || true\n\
chmod -R 777 storage/\n\
chmod -R 777 bootstrap/cache/\n\
chmod 666 database/database.sqlite || true\n\
echo "🚀 Starting Apache..."\n\
apache2-foreground' > /start.sh && chmod +x /start.sh


CMD ["/start.sh"]