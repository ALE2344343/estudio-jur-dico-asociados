FROM php:8.2-cli-alpine

# Instalar dependencias del sistema y extensiones de PHP requeridas por Laravel y MySQL
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    oniguruma-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Directorio de trabajo
WORKDIR /var/www

# Copiar archivos del proyecto
COPY . /var/www

# Instalar dependencias de Laravel sin las de desarrollo
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permisos para storage y bootstrap/cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Puerto expuesto por Render (por defecto Render asigna la variable $PORT o 10000)
ENV PORT=10000
EXPOSE 10000

# Script de arranque
CMD php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear \
    && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}
