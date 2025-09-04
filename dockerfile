FROM php:8.2-alpine

# Instalar dependencias del sistema
RUN apk add --no-cache \
    bash \
    curl \
    git \
    libzip-dev \
    postgresql-dev \
    zip

# Instalar extensiones de PHP necesarias
RUN docker-php-ext-install pdo pdo_pgsql zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Copiar el código de la aplicación
COPY . .

# Instalar dependencias de Composer (sin dev)
RUN composer install --optimize-autoloader --no-dev

# Configurar permisos
RUN chmod -R 775 storage bootstrap/cache

# Puerto expuesto
EXPOSE 8000

# Comando para ejecutar la aplicación
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8000
