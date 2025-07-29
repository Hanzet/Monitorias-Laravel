FROM php:8.2-fpm

# Argumentos para configuración
ARG user=laravel
ARG uid=1000

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    supervisor \
    nginx \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crear usuario del sistema para ejecutar comandos de Composer y Artisan
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Establecer directorio de trabajo
WORKDIR /var/www

# Copiar archivos del proyecto
COPY . /var/www

# Instalar dependencias de Composer
RUN composer install --no-dev --optimize-autoloader

# Cambiar propietario de los archivos
RUN chown -R $user:$user /var/www

# Configurar permisos
RUN chmod -R 755 /var/www/storage
RUN chmod -R 755 /var/www/bootstrap/cache

# Exponer puerto 9000 para PHP-FPM
EXPOSE 9000

# Cambiar al usuario laravel
USER $user

# Comando por defecto
CMD ["php-fpm"] 