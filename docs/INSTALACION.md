# Guía de Instalación Completa

## 🚀 Instalación Paso a Paso

### Paso 1: Clonar el Repositorio

```bash
# Clonar el repositorio
git clone <URL_DEL_REPOSITORIO> monitorias
cd monitorias

# O si ya tienes el código, navegar al directorio
cd /ruta/a/tu/proyecto/monitorias
```

### Paso 2: Verificar Estructura del Proyecto

```bash
# Verificar que tienes todos los archivos necesarios
ls -la

# Deberías ver:
# - docker-compose.yml
# - Dockerfile
# - composer.json
# - .gitignore
# - carpeta app/
# - carpeta config/
# - carpeta database/
# - carpeta routes/
```

### Paso 3: Crear Archivo de Configuración (.env)

```bash
# Crear el archivo .env
touch .env
```

**Contenido del archivo `.env`:**

```env
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=monitorias
DB_USERNAME=monitorias
DB_PASSWORD=root

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME="${APP_NAME}"
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

### Paso 4: Construir y Levantar Contenedores

```bash
# Detener contenedores existentes (si los hay)
docker-compose down

# Construir y levantar contenedores
docker-compose up -d --build
```

**Explicación de los servicios:**

-   `app`: Contenedor de Laravel con PHP 8.2
-   `webserver`: Nginx como servidor web
-   `db`: MySQL 8.0 como base de datos
-   `redis`: Redis para caché y sesiones
-   `phpmyadmin`: Interfaz web para gestionar MySQL

### Paso 5: Verificar Estado de Contenedores

```bash
# Verificar que todos los contenedores estén ejecutándose
docker ps

# Deberías ver 5 contenedores ejecutándose:
# - monitorias_app
# - monitorias_nginx
# - monitorias_db
# - monitorias_redis
# - monitorias_phpmyadmin
```

### Paso 6: Configurar Laravel

```bash
# Generar clave de aplicación
docker exec monitorias_app php artisan key:generate

# Limpiar caché de configuración
docker exec monitorias_app php artisan config:clear
docker exec monitorias_app php artisan cache:clear

# Ejecutar migraciones
docker exec monitorias_app php artisan migrate

# Verificar estado de migraciones
docker exec monitorias_app php artisan migrate:status
```

### Paso 7: Verificar Instalación

```bash
# Verificar que Laravel funcione
curl http://localhost:8000

# Verificar que phpMyAdmin esté disponible
curl http://localhost:8080
```

## 🔧 Configuración Adicional

### Configurar Permisos (si es necesario)

```bash
# Dar permisos a las carpetas de Laravel
docker exec monitorias_app chmod -R 755 /var/www/storage
docker exec monitorias_app chmod -R 755 /var/www/bootstrap/cache
```

### Instalar Dependencias de Desarrollo (Opcional)

```bash
# Si necesitas dependencias de desarrollo
docker exec monitorias_app composer install --dev
```

### Configurar Seeders (Opcional)

```bash
# Ejecutar seeders para datos de prueba
docker exec monitorias_app php artisan db:seed
```

## 🌐 Acceso a los Servicios

Una vez instalado, puedes acceder a:

| Servicio       | URL                       | Descripción              |
| -------------- | ------------------------- | ------------------------ |
| **Laravel**    | http://localhost:8000     | Aplicación principal     |
| **phpMyAdmin** | http://localhost:8080     | Gestión de base de datos |
| **API**        | http://localhost:8000/api | Endpoints de la API      |

**Credenciales de phpMyAdmin:**

-   **Usuario**: monitorias
-   **Contraseña**: root
-   **Servidor**: db

## 🚨 Solución de Problemas Comunes

### Error: "Connection refused" en base de datos

```bash
# Esperar a que MySQL se inicialice completamente
sleep 30
docker exec monitorias_app php artisan migrate
```

### Error: "APP_KEY not set"

```bash
# Regenerar clave de aplicación
docker exec monitorias_app php artisan key:generate
```

### Error: "Permission denied"

```bash
# Corregir permisos
docker exec monitorias_app chown -R laravel:laravel /var/www
```

### Contenedores no se levantan

```bash
# Verificar logs
docker-compose logs

# Reconstruir desde cero
docker-compose down -v
docker-compose up -d --build
```

## 📋 Comandos Útiles

### Gestión de Contenedores

```bash
# Ver logs en tiempo real
docker-compose logs -f

# Detener todos los servicios
docker-compose down

# Reiniciar un servicio específico
docker-compose restart app

# Ver logs de un servicio específico
docker-compose logs app
```

### Comandos de Laravel

```bash
# Ejecutar comandos de Artisan
docker exec monitorias_app php artisan [comando]

# Ejecutar Tinker
docker exec -it monitorias_app php artisan tinker

# Ver rutas disponibles
docker exec monitorias_app php artisan route:list
```

### Gestión de Base de Datos

```bash
# Acceder a MySQL
docker exec -it monitorias_db mysql -u monitorias -p

# Crear backup
docker exec monitorias_db mysqldump -u monitorias -p monitorias > backup.sql

# Restaurar backup
docker exec -i monitorias_db mysql -u monitorias -p monitorias < backup.sql
```

## ✅ Verificación Final

Para verificar que todo funciona correctamente:

1. **Laravel**: http://localhost:8000 debe mostrar la página de bienvenida
2. **phpMyAdmin**: http://localhost:8080 debe permitir acceso a la base de datos
3. **API**: Las rutas de la API deben responder correctamente
4. **Logs**: No deben aparecer errores en los logs

```bash
# Verificar logs de todos los servicios
docker-compose logs --tail=50
```

## 🎉 ¡Instalación Completada!

Si llegaste hasta aquí sin errores, tu entorno de desarrollo está listo para usar.
