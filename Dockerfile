FROM php:8.2-apache

# Install dependencies including zip/unzip and standard PHP extensions
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
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo_mysql pdo_pgsql zip bcmath intl calendar exif pcntl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Node.js (Required for Vite build)
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && \
    apt-get install -y nodejs

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Configure Apache Document Root to point to /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# Enable Apache Rewrite Module
RUN a2enmod rewrite

# Enable Apache SSL Module, generate a self-signed cert, and configure default-ssl
RUN a2enmod ssl socache_shmcb \
    && openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/ssl/private/apache-selfsigned.key \
    -out /etc/ssl/certs/apache-selfsigned.crt \
    -subj "/C=ET/ST=Addis/L=Addis Ababa/O=Renaissance/CN=localhost" \
    && sed -i 's|/etc/ssl/certs/ssl-cert-snakeoil.pem|/etc/ssl/certs/apache-selfsigned.crt|g' /etc/apache2/sites-available/default-ssl.conf \
    && sed -i 's|/etc/ssl/private/ssl-cert-snakeoil.key|/etc/ssl/private/apache-selfsigned.key|g' /etc/apache2/sites-available/default-ssl.conf \
    && sed -ri -e 's|/var/www/html|${APACHE_DOCUMENT_ROOT}|g' /etc/apache2/sites-available/default-ssl.conf \
    && a2ensite default-ssl

# Set Working Directory
WORKDIR /var/www/html

# Set Composer environment variables to prevent timeout issues on slow connections
ENV COMPOSER_PROCESS_TIMEOUT=2000
ENV COMPOSER_HTTP_TIMEOUT=2000

# Copy composer files first for better layer caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy package files and build assets
COPY package.json package-lock.json ./
RUN npm install

# Copy remaining project files
COPY . /var/www/html

# Create .env from template for Docker
RUN cp docker/.env.docker .env

# Run composer scripts now that all files are present
# dump-autoload regenerates the classmap; package:discover rebuilds
# the package manifest WITHOUT dev packages (since --no-dev was used)
RUN composer dump-autoload --optimize \
    && php artisan package:discover --ansi

# Build frontend assets
RUN npm run build

# Set permissions only for directories Laravel needs to write to
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Run migrations and start Apache
CMD bash -c "chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache && php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan migrate --force && php artisan storage:link --force && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && apache2-foreground"
