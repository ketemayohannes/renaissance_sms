# ============================================================
# Stage 1 — Node.js: Install npm deps and compile Vite assets
# ============================================================
FROM node:22-alpine AS assets

WORKDIR /app

# Copy package files first for better layer caching
COPY package.json package-lock.json ./
RUN npm install

# Copy full source so Vite can see all JS/CSS files
COPY . .

# Compile assets into public/build
RUN npm run build


# ============================================================
# Stage 2 — PHP/Apache: Production application image
# ============================================================
FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpq-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libicu-dev \
    zip \
    unzip \
    git \
    curl \
    openssl \
    supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo_mysql pdo_pgsql zip bcmath intl calendar exif pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Configure Apache Document Root to point to /public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# Enable Apache Rewrite Module
RUN a2enmod rewrite

# Enable Apache SSL, generate self-signed cert, and configure default-ssl
RUN a2enmod ssl socache_shmcb \
    && openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
        -keyout /etc/ssl/private/apache-selfsigned.key \
        -out /etc/ssl/certs/apache-selfsigned.crt \
        -subj "/C=ET/ST=Addis/L=Addis Ababa/O=Renaissance/CN=localhost" \
    && sed -i 's|/etc/ssl/certs/ssl-cert-snakeoil.pem|/etc/ssl/certs/apache-selfsigned.crt|g' /etc/apache2/sites-available/default-ssl.conf \
    && sed -i 's|/etc/ssl/private/ssl-cert-snakeoil.key|/etc/ssl/private/apache-selfsigned.key|g' /etc/apache2/sites-available/default-ssl.conf \
    && sed -ri -e 's|/var/www/html|${APACHE_DOCUMENT_ROOT}|g' /etc/apache2/sites-available/default-ssl.conf \
    && a2ensite default-ssl

WORKDIR /var/www/html

# Set Composer environment variables to prevent timeout issues on slow connections
ENV COMPOSER_PROCESS_TIMEOUT=2000
ENV COMPOSER_HTTP_TIMEOUT=2000

# Copy composer files and install PHP dependencies (no dev, optimized)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy the full application source
COPY . /var/www/html

# Copy compiled frontend assets from the Node stage (no Node.js needed here)
COPY --from=assets /app/public/build /var/www/html/public/build

# Create .env from Docker template
RUN cp docker/.env.docker .env

# Finalize Composer (autoload classmap + package discovery)
RUN composer dump-autoload --optimize \
    && php artisan package:discover --ansi

# Set correct permissions for storage and cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy Supervisor config (manages Apache + queue workers)
COPY docker/supervisord.conf /etc/supervisor/conf.d/renaissance.conf

# Startup: fix permissions, cache config/routes/views, run migrations, then start Supervisor
CMD bash -c "\
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan migrate --force \
    && php artisan storage:link --force \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && supervisord -n -c /etc/supervisor/supervisord.conf"
