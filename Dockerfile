# ============================================================================
# SatsetUI Docker Image — php:8.4-apache + Node.js 22 LTS
# ============================================================================
# App-only container. Database, Redis, etc. run on the host machine.
# Use host.docker.internal to connect from inside the container.
# Node.js is included for workspace preview (npm install + vite dev server).
# ============================================================================

FROM php:8.4-apache

LABEL maintainer="SatsetUI"

# ---------- System dependencies ----------
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        curl \
        unzip \
        zip \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libonig-dev \
        libxml2-dev \
        libzip-dev \
        libpq-dev \
        libsqlite3-dev \
        supervisor \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# ---------- Node.js 22 LTS (for workspace preview dev servers) ----------
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/* \
    && node --version && npm --version

# ---------- PHP extensions ----------
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pdo_mysql \
        pdo_sqlite \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        opcache

# ---------- Composer ----------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ---------- Apache configuration ----------
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

# Enable required Apache modules
RUN a2enmod rewrite headers expires

# Create the vhost config
RUN echo '<VirtualHost *:80>\n\
    ServerAdmin webmaster@localhost\n\
    DocumentRoot ${APACHE_DOCUMENT_ROOT}\n\
    \n\
    <Directory ${APACHE_DOCUMENT_ROOT}>\n\
        AllowOverride All\n\
        Require all granted\n\
        FallbackResource /index.php\n\
    </Directory>\n\
    \n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Update Apache to use the correct document root
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf 2>/dev/null || true

# ---------- PHP production settings ----------
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Optimized php.ini overrides
RUN echo '\n\
upload_max_filesize = 64M\n\
post_max_size = 64M\n\
memory_limit = 512M\n\
max_execution_time = 300\n\
max_input_time = 300\n\
opcache.enable=1\n\
opcache.memory_consumption=256\n\
opcache.max_accelerated_files=20000\n\
opcache.validate_timestamps=0\n\
' >> "$PHP_INI_DIR/php.ini"

# ---------- Working directory ----------
WORKDIR /var/www/html

# ---------- Copy application ----------
COPY --chown=www-data:www-data . .

# ---------- Install PHP dependencies ----------
RUN composer install --optimize-autoloader --no-dev --no-interaction --prefer-dist

# ---------- Install Node dependencies & build frontend ----------
RUN npm ci --no-audit --no-fund && npm run build

# ---------- Laravel optimizations (route + view only, config is cached at startup) ----------
# NOTE: config:cache is done at container start via entrypoint.sh
# because runtime env vars (DB_HOST, REDIS_HOST, etc.) differ from build time.
RUN php artisan route:cache || true \
    && php artisan view:cache || true

# ---------- Storage & permissions ----------
RUN mkdir -p storage/app/workspaces \
    && mkdir -p storage/framework/{cache,sessions,views} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# ---------- Supervisor config (Apache + Queue Worker) ----------
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# ---------- Entrypoint script ----------
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# ---------- Expose & run ----------
EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
