# 🎓 Sistema de Monitorías - Laravel con Docker

## 📋 Descripción del Proyecto

Sistema de gestión de monitorías desarrollado con Laravel 12 y Docker. Este proyecto incluye una API REST completa para la gestión de usuarios, autenticación y monitorías académicas.

## 🐳 Configuración con Docker

### Requisitos Previos

- **Docker Desktop** instalado y funcionando
- **Git** para clonar el repositorio
- **WSL2** habilitado (recomendado para Windows)

### 🚀 Inicio Rápido

1. **Clonar el proyecto**
   ```bash
   git clone <tu-repositorio>
   cd monitorias
   ```

2. **Configurar variables de entorno**
   ```bash
   cp .env.example .env
   ```

3. **Editar `.env` para Docker**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=db
   DB_PORT=3306
   DB_DATABASE=monitorias
   DB_USERNAME=monitorias
   DB_PASSWORD=root
   
   REDIS_HOST=redis
   REDIS_PORT=6379
   
   APP_ENV=local
   APP_DEBUG=true
   ```

4. **Inicializar con Docker**
   ```bash
   # Usando Makefile (recomendado)
   make init
   
   # O manualmente
   docker-compose up -d --build
   ```

## 🛠️ Comandos Principales

### Usando Makefile (Recomendado)

```bash
make help          # Ver todos los comandos disponibles
make up            # Levantar contenedores
make down          # Detener contenedores
make restart       # Reiniciar contenedores
make logs          # Ver logs en tiempo real
make shell         # Acceder al contenedor de la aplicación
make migrate       # Ejecutar migraciones
make seed          # Ejecutar seeders
make test          # Ejecutar tests
make cache         # Limpiar caché de Laravel
make optimize      # Optimizar para producción
```

### Usando Docker Compose

```bash
# Gestión de contenedores
docker-compose up -d          # Levantar en background
docker-compose down           # Detener
docker-compose restart        # Reiniciar
docker-compose logs -f        # Ver logs

# Comandos de Laravel
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app composer install
docker-compose exec app php artisan test
```

## 🌐 URLs de Acceso

| Servicio        | URL                       | Descripción              |
| --------------- | ------------------------- | ------------------------ |
| **Laravel App** | http://localhost:8000     | Aplicación principal     |
| **phpMyAdmin**  | http://localhost:8080     | Gestión de base de datos |
| **API Docs**    | http://localhost:8000/api | Documentación de la API  |

## 🗄️ Base de Datos

### Credenciales MySQL

- **Host:** `db` (dentro de Docker) o `localhost` (desde tu máquina)
- **Puerto:** `3307` (externo) / `3306` (interno)
- **Base de datos:** `monitorias`
- **Usuario:** `monitorias`
- **Contraseña:** `root`

### Acceso desde phpMyAdmin

- **URL:** http://localhost:8080
- **Usuario:** `monitorias`
- **Contraseña:** `root`

## 🔧 Configuración de Servicios

### Estructura de Docker

```
docker/
├── nginx/
│   └── conf.d/
│       └── app.conf          # Configuración de Nginx
├── php/
│   └── local.ini             # Configuración de PHP
├── mysql/
│   ├── data/                 # Datos de MySQL
│   └── my.cnf                # Configuración de MySQL
└── init.sh                   # Script de inicialización
```

### Servicios Incluidos

| Servicio    | Imagen           | Puerto | Descripción                    |
| ----------- | ---------------- | ------ | ------------------------------ |
| **app**     | monitorias-app   | 9000   | Laravel con PHP 8.2-FPM        |
| **nginx**   | nginx:alpine     | 8000   | Servidor web                   |
| **db**      | mysql:8.0        | 3307   | Base de datos MySQL            |
| **redis**   | redis:alpine     | 6379   | Caché y sesiones               |
| **phpmyadmin** | phpmyadmin/phpmyadmin | 8080 | Gestión de BD                |

## 🚨 Problemas Resueltos

### 1. Error de Laravel Telescope

**Problema:** Error durante la construcción de Docker:
```
Class "Laravel\Telescope\TelescopeApplicationServiceProvider" not found
```

**Solución:** Removimos el `TelescopeServiceProvider` del registro de providers en `bootstrap/providers.php` ya que Laravel Telescope está en `require-dev` y no se instala en producción.

**Archivo modificado:** `bootstrap/providers.php`
```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    // TelescopeServiceProvider removido
];
```

### 2. Conflicto de Puertos MySQL

**Problema:** Puerto 3306 ocupado por XAMPP
```
Error response from daemon: ports are not available: exposing port TCP 0.0.0.0:3306
```

**Solución:** Cambiamos el puerto externo de MySQL de 3306 a 3307 en `docker-compose.yml`.

**Archivo modificado:** `docker-compose.yml`
```yaml
ports:
  - "3307:3306"  # Puerto externo 3307, interno 3306
```

## 🔍 Troubleshooting

### Problemas Comunes

#### 1. Puerto 8000 ocupado
```bash
# Cambiar puerto en docker-compose.yml
ports:
  - "8001:80"  # Cambiar 8000 por 8001
```

#### 2. Permisos de archivos
```bash
make permissions
# O
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

#### 3. Limpiar caché
```bash
make cache
# O
docker-compose exec app php artisan cache:clear
```

#### 4. Reconstruir contenedores
```bash
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Logs y Debugging

```bash
# Ver logs de todos los servicios
make logs

# Ver logs de un servicio específico
docker-compose logs app
docker-compose logs nginx
docker-compose logs db
```

## 📊 Monitoreo

### Estado de contenedores
```bash
make status
# O
docker-compose ps
```

### Uso de recursos
```bash
docker stats
```

## 🚀 Desarrollo

### Acceder al contenedor
```bash
make shell
# O
docker-compose exec app bash
```

### Instalar nuevas dependencias
```bash
make composer cmd="require package/name"
# O
docker-compose exec app composer require package/name
```

### Ejecutar comandos de Artisan
```bash
make artisan cmd="make:controller UserController"
# O
docker-compose exec app php artisan make:controller UserController
```

### Ejecutar tests
```bash
make test
# O
docker-compose exec app php artisan test
```

## 🔒 Seguridad

### Cambiar credenciales por defecto

1. Editar `docker-compose.yml`
2. Cambiar `DB_PASSWORD` y `MYSQL_ROOT_PASSWORD`
3. Actualizar `.env`
4. Reconstruir contenedores

### Configurar SSL

Agregar certificados SSL en la configuración de Nginx.

## 📚 Documentación Adicional

- [API Documentation](API_DOCUMENTATION.md) - Documentación completa de la API
- [Postman Collection](postman_collection.json) - Colección de Postman para testing
- [Docker README](DOCKER_README.md) - Guía detallada de Docker
- [Postman Routes](POSTMAN_ROUTES.md) - Rutas disponibles en Postman

## 🛠️ Tecnologías Utilizadas

- **Backend:** Laravel 12
- **Base de Datos:** MySQL 8.0
- **Caché:** Redis
- **Servidor Web:** Nginx
- **PHP:** 8.2-FPM
- **Contenedores:** Docker & Docker Compose
- **Autenticación:** Laravel Sanctum

## 🤝 Contribuir

1. Fork el proyecto
2. Crear una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abrir un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

---

**¡Disfruta desarrollando con Docker! 🐳**
