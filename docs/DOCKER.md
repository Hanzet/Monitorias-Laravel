# Configuración de Docker

## 🐳 Arquitectura Docker

El proyecto utiliza Docker Compose para orquestar múltiples servicios:

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Nginx (80)    │    │  Laravel App    │    │   MySQL (3306)  │
│   Port: 8000    │◄──►│   Port: 9000    │◄──►│   Port: 3310    │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                                │
                                ▼
                       ┌─────────────────┐
                       │   Redis (6379)  │
                       │   Port: 6379    │
                       └─────────────────┘
                                │
                                ▼
                       ┌─────────────────┐
                       │  phpMyAdmin     │
                       │   Port: 8080    │
                       └─────────────────┘
```

## 📁 Estructura de Archivos Docker

```
monitorias/
├── docker-compose.yml          # Configuración principal de servicios
├── Dockerfile                  # Imagen personalizada de Laravel
├── .dockerignore              # Archivos a ignorar en Docker
├── docker/
│   ├── nginx/
│   │   └── conf.d/
│   │       └── app.conf       # Configuración de Nginx
│   ├── mysql/
│   │   ├── data/              # Datos persistentes de MySQL
│   │   └── my.cnf             # Configuración de MySQL
│   └── php/
│       └── local.ini          # Configuración de PHP
└── docker-compose.override.yml # Configuraciones adicionales (opcional)
```

## 🔧 Configuración de Servicios

### 1. Servicio de Aplicación (Laravel)

```yaml
app:
    build:
        context: .
        dockerfile: Dockerfile
    container_name: monitorias_app
    restart: unless-stopped
    working_dir: /var/www
    volumes:
        - ./:/var/www
        - ./docker/php/local.ini:/usr/local/etc/php/conf.d/local.ini
    networks:
        - monitorias_network
```

**Características:**

-   **Imagen base**: PHP 8.2-FPM
-   **Usuario**: laravel (UID 1000)
-   **Directorio de trabajo**: /var/www
-   **Volúmenes**: Código fuente y configuración de PHP

### 2. Servidor Web (Nginx)

```yaml
webserver:
    image: nginx:alpine
    container_name: monitorias_nginx
    restart: unless-stopped
    ports:
        - "8000:80"
    volumes:
        - ./:/var/www
        - ./docker/nginx/conf.d/:/etc/nginx/conf.d/
    networks:
        - monitorias_network
    depends_on:
        - app
```

**Características:**

-   **Imagen**: nginx:alpine (ligera)
-   **Puerto**: 8000 (host) → 80 (contenedor)
-   **Configuración**: Archivos en docker/nginx/conf.d/

### 3. Base de Datos (MySQL)

```yaml
db:
    image: mysql:8.0
    container_name: monitorias_db
    restart: unless-stopped
    environment:
        MYSQL_DATABASE: ${DB_DATABASE:-monitorias}
        MYSQL_ROOT_PASSWORD: ${DB_PASSWORD:-root}
        MYSQL_PASSWORD: ${DB_PASSWORD:-root}
        MYSQL_USER: ${DB_USERNAME:-monitorias}
    ports:
        - "3310:3306"
    volumes:
        - ./docker/mysql/data:/var/lib/mysql
        - ./docker/mysql/my.cnf:/etc/mysql/my.cnf
    networks:
        - monitorias_network
```

**Características:**

-   **Imagen**: MySQL 8.0
-   **Puerto**: 3310 (host) → 3306 (contenedor)
-   **Datos persistentes**: ./docker/mysql/data/
-   **Variables de entorno**: Configurables desde .env

### 4. Redis (Caché y Sesiones)

```yaml
redis:
    image: redis:alpine
    container_name: monitorias_redis
    restart: unless-stopped
    ports:
        - "6379:6379"
    networks:
        - monitorias_network
```

**Características:**

-   **Imagen**: redis:alpine
-   **Puerto**: 6379 (accesible desde host)
-   **Uso**: Caché, sesiones, colas

### 5. phpMyAdmin

```yaml
phpmyadmin:
    image: phpmyadmin/phpmyadmin
    container_name: monitorias_phpmyadmin
    restart: unless-stopped
    ports:
        - "8080:80"
    environment:
        PMA_HOST: db
        PMA_PORT: 3306
        PMA_USER: ${DB_USERNAME:-monitorias}
        PMA_PASSWORD: ${DB_PASSWORD:-root}
    networks:
        - monitorias_network
    depends_on:
        - db
```

**Características:**

-   **Imagen**: phpmyadmin/phpmyadmin
-   **Puerto**: 8080 (host) → 80 (contenedor)
-   **Acceso**: http://localhost:8080

## 🐋 Dockerfile Detallado

```dockerfile
# Etapa 1: Construcción de dependencias
FROM composer:latest AS composer

# Etapa 2: Imagen final
FROM php:8.2-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Instalar extensiones de PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Copiar Composer desde la imagen oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crear usuario laravel
RUN useradd -G www-data,root -u 1000 -d /home/laravel laravel
RUN mkdir -p /home/laravel/.composer && \
    chown -R laravel:laravel /home/laravel

# Establecer directorio de trabajo
WORKDIR /var/www

# Copiar código de la aplicación
COPY . /var/www

# Instalar dependencias de Composer
RUN composer install --no-dev --optimize-autoloader

# Configurar permisos
RUN chown -R laravel:laravel /var/www
RUN chmod -R 755 /var/www/storage
RUN chmod -R 755 /var/www/bootstrap/cache

# Cambiar al usuario laravel
USER laravel
```

## 🌐 Configuración de Nginx

**Archivo: `docker/nginx/conf.d/app.conf`**

```nginx
server {
    listen 80;
    index index.php index.html;
    error_log  /var/log/nginx/error.log;
    access_log /var/log/nginx/access.log;
    root /var/www/public;

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
        gzip_static on;
    }
}
```

## ⚙️ Configuración de PHP

**Archivo: `docker/php/local.ini`**

```ini
upload_max_filesize=40M
post_max_size=40M
memory_limit=512M
max_execution_time=600
max_input_vars=3000
```

## 🔧 Configuración de MySQL

**Archivo: `docker/mysql/my.cnf`**

```ini
[mysqld]
general_log = 1
general_log_file = /var/lib/mysql/general.log
default-authentication-plugin=mysql_native_password
```

## 🌐 Redes Docker

```yaml
networks:
    monitorias_network:
        driver: bridge
```

**Características de la red:**

-   **Tipo**: Bridge (comunicación entre contenedores)
-   **Nombre**: monitorias_network
-   **Acceso**: Los contenedores pueden comunicarse por nombre

## 📊 Puertos y Acceso

| Servicio   | Puerto Host | Puerto Contenedor | URL de Acceso         |
| ---------- | ----------- | ----------------- | --------------------- |
| Nginx      | 8000        | 80                | http://localhost:8000 |
| phpMyAdmin | 8080        | 80                | http://localhost:8080 |
| MySQL      | 3310        | 3306              | localhost:3310        |
| Redis      | 6379        | 6379              | localhost:6379        |

## 🔍 Comandos de Diagnóstico

### Verificar Estado de Contenedores

```bash
# Ver todos los contenedores
docker ps

# Ver contenedores incluyendo los detenidos
docker ps -a

# Ver logs de un contenedor específico
docker logs monitorias_app
```

### Verificar Redes

```bash
# Ver redes disponibles
docker network ls

# Inspeccionar red específica
docker network inspect monitorias_monitorias_network
```

### Verificar Volúmenes

```bash
# Ver volúmenes
docker volume ls

# Inspeccionar volumen
docker volume inspect monitorias_dbdata
```

### Verificar Imágenes

```bash
# Ver imágenes disponibles
docker images

# Ver historial de una imagen
docker history monitorias-app
```

## 🚨 Solución de Problemas

### Problema: Contenedor no se inicia

```bash
# Ver logs del contenedor
docker logs monitorias_app

# Verificar configuración
docker-compose config
```

### Problema: Puerto ocupado

```bash
# Ver qué usa el puerto
netstat -tulpn | grep :8000

# Cambiar puerto en docker-compose.yml
ports:
  - "8001:80"  # Cambiar 8000 por 8001
```

### Problema: Permisos de archivos

```bash
# Corregir permisos
docker exec monitorias_app chown -R laravel:laravel /var/www
docker exec monitorias_app chmod -R 755 /var/www/storage
```

### Problema: Memoria insuficiente

```bash
# Aumentar memoria en Docker Desktop
# Settings > Resources > Memory: 4GB o más
```

## 🔄 Comandos de Mantenimiento

### Limpiar Docker

```bash
# Eliminar contenedores no utilizados
docker container prune

# Eliminar imágenes no utilizadas
docker image prune

# Eliminar volúmenes no utilizados
docker volume prune

# Limpieza completa
docker system prune -a
```

### Backup y Restore

```bash
# Backup de base de datos
docker exec monitorias_db mysqldump -u monitorias -p monitorias > backup.sql

# Restore de base de datos
docker exec -i monitorias_db mysql -u monitorias -p monitorias < backup.sql
```

### Actualizar Imágenes

```bash
# Actualizar todas las imágenes
docker-compose pull

# Reconstruir con nuevas imágenes
docker-compose up -d --build
```

## 📈 Optimización

### Para Desarrollo

-   Usar volúmenes para código fuente (cambio en tiempo real)
-   Configurar Xdebug para debugging
-   Habilitar logs detallados

### Para Producción

-   Usar imágenes multi-stage
-   Optimizar tamaño de imágenes
-   Configurar health checks
-   Usar secrets para credenciales
