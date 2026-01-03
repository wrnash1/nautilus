# Nautilus Dive Shop Management System - Development Container
# PHP 8.4 with Apache and all required extensions

FROM php:8.4-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    mariadb-client \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions required by Nautilus
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mysqli \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# Enable Apache modules
RUN a2enmod rewrite headers ssl

# Set recommended PHP.ini settings for development
RUN { \
    echo 'memory_limit = 512M'; \
    echo 'upload_max_filesize = 20M'; \
    echo 'post_max_size = 20M'; \
    echo 'max_execution_time = 600'; \
    echo 'max_input_time = 600'; \
    echo 'default_socket_timeout = 600'; \
    echo 'display_errors = On'; \
    echo 'display_startup_errors = On'; \
    echo 'error_reporting = E_ALL'; \
    echo 'log_errors = On'; \
    echo 'error_log = /var/log/php_errors.log'; \
} > /usr/local/etc/php/conf.d/nautilus.ini

# Install Composer
COPY --from=docker.io/composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Configure Apache to point to public directory
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Add custom Apache configuration
RUN { \
    echo '<Directory /var/www/html/public>'; \
    echo '    Options -Indexes +FollowSymLinks'; \
    echo '    AllowOverride All'; \
    echo '    Require all granted'; \
    echo '</Directory>'; \
    echo ''; \
    echo '<DirectoryMatch "^/var/www/html/(?!public)">'; \
    echo '    Require all denied'; \
    echo '</DirectoryMatch>'; \
} >> /etc/apache2/sites-available/000-default.conf

# Create necessary directories with correct permissions
RUN mkdir -p /var/www/html/storage/logs \
    /var/www/html/storage/sessions \
    /var/www/html/storage/cache \
    /var/www/html/public/uploads \
    /var/www/html/public/uploads

# Copy entrypoint script
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose port 80
EXPOSE 80

# Use entrypoint to fix permissions on startup
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Start Apache
CMD ["apache2-foreground"]
