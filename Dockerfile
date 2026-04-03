# TicketsCAD - Computer Aided Dispatch System
# Multi-stage Docker build supporting PHP 7.4 through 8.4
#
# Build: docker build -t ticketscad .
# Run:   docker compose up -d
#
# Default: PHP 8.2 with Apache on Debian Bookworm

FROM php:8.2-apache-bookworm

LABEL maintainer="Eric Osterberg <ejosterberg@gmail.com>"
LABEL org.opencontainers.image.source="https://github.com/openises/tickets"
LABEL org.opencontainers.image.description="TicketsCAD - Free Open Source Computer Aided Dispatch"
LABEL org.opencontainers.image.licenses="GPL-2.0"

# Install PHP extensions and dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    libxml2-dev \
    libonig-dev \
    libcurl4-openssl-dev \
    libssl-dev \
    zlib1g-dev \
    unzip \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        mysqli \
        pdo \
        pdo_mysql \
        gd \
        zip \
        xml \
        mbstring \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite headers expires

# PHP configuration for production
RUN { \
    echo 'display_errors = Off'; \
    echo 'log_errors = On'; \
    echo 'error_log = /var/log/php_errors.log'; \
    echo 'error_reporting = E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED'; \
    echo 'max_execution_time = 300'; \
    echo 'max_input_time = 300'; \
    echo 'memory_limit = 256M'; \
    echo 'post_max_size = 50M'; \
    echo 'upload_max_filesize = 30M'; \
    echo 'session.cookie_httponly = 1'; \
    echo 'session.use_strict_mode = 1'; \
    echo 'date.timezone = UTC'; \
} > /usr/local/etc/php/conf.d/ticketscad.ini

# Apache configuration
RUN { \
    echo '<Directory /var/www/html>'; \
    echo '    Options -Indexes +FollowSymLinks'; \
    echo '    AllowOverride All'; \
    echo '    Require all granted'; \
    echo '</Directory>'; \
    echo ''; \
    echo 'ServerTokens Prod'; \
    echo 'ServerSignature Off'; \
    echo 'Header always set X-Content-Type-Options "nosniff"'; \
    echo 'Header always set X-Frame-Options "SAMEORIGIN"'; \
    echo 'Header always set X-XSS-Protection "1; mode=block"'; \
    echo 'Header always set Referrer-Policy "strict-origin-when-cross-origin"'; \
} > /etc/apache2/conf-available/ticketscad.conf \
    && a2enconf ticketscad

# Copy application code (sensitive files excluded via .dockerignore)
COPY . /var/www/html/

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && mkdir -p /var/www/html/uploads \
    && chown www-data:www-data /var/www/html/uploads \
    && chmod 775 /var/www/html/uploads

# Create entrypoint script for auto-configuration
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

# Note: entrypoint runs as root to write config files and create directories.
# Apache itself runs as www-data via its own configuration.
# The USER directive is intentionally omitted — the entrypoint handles permissions.

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
