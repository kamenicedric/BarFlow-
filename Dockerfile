# BarFlow - image de production (Apache + PHP 8.2)
FROM php:8.2-apache

# Extensions PHP requises (MySQL, GD pour images/logo, zip pour composer)
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpng-dev libjpeg-dev libfreetype6-dev libzip-dev unzip ca-certificates \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql gd zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Installer les dependances d'abord (cache Docker)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copier le reste du projet
COPY . .
RUN composer dump-autoload --optimize --no-dev

# Docroot Apache -> /public + vhost avec AllowOverride All
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf

# Permissions ecriture (logs, cache PDF, uploads)
RUN chown -R www-data:www-data storage public/assets/uploads \
    && chmod -R 775 storage public/assets/uploads

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80
CMD ["entrypoint.sh"]
