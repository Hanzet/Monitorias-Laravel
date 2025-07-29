# 🐳 Laravel con Docker - Guía Completa

## 📋 Requisitos Previos

-   Docker Desktop instalado y funcionando
-   WSL2 habilitado (recomendado)
-   Git

## 🚀 Inicio Rápido

### **1. Clonar y configurar el proyecto**

```bash
# Clonar el proyecto (si no lo tienes)
git clone <tu-repositorio>
cd monitorias

# Copiar archivo de configuración
cp .env.example .env
```

### **2. Configurar variables de entorno**

Editar el archivo `.env` con estas configuraciones para Docker:

```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=monitorias
DB_USERNAME=monitorias
DB_PASSWORD=root

REDIS_HOST=redis
REDIS_PORT=6379
```

### **3. Inicializar con Docker**

```bash
# Construir y levantar contenedores
make init

# O manualmente:
docker-compose up -d
docker-compose exec app bash /var/www/docker/init.sh
```

## 🎯 Comandos Principales

### **Usando Makefile (Recomendado)**

```bash
make help          # Ver todos los comandos disponibles
make up            # Levantar contenedores
make down          # Detener contenedores
make restart       # Reiniciar contenedores
make logs          # Ver logs
make shell         # Acceder al contenedor
make migrate       # Ejecutar migraciones
make seed          # Ejecutar seeders
make test          # Ejecutar tests
```

### **Usando Docker Compose directamente**

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

### **Credenciales MySQL**

-   **Host:** `db` (dentro de Docker) o `localhost` (desde tu máquina)
-   **Puerto:** `3306`
-   **Base de datos:** `monitorias`
-   **Usuario:** `monitorias`
-   **Contraseña:** `root`

### **Acceso desde phpMyAdmin**

-   URL: http://localhost:8080
-   Usuario: `monitorias`
-   Contraseña: `root`

## 🔧 Configuración Avanzada

### **Estructura de Docker**

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

### **Servicios Incluidos**

-   **app:** Laravel con PHP 8.2-FPM
-   **webserver:** Nginx
-   **db:** MySQL 8.0
-   **redis:** Redis para caché
-   **phpmyadmin:** Gestión de base de datos

## 🛠️ Desarrollo

### **Acceder al contenedor**

```bash
make shell
# O
docker-compose exec app bash
```

### **Instalar nuevas dependencias**

```bash
make composer cmd="require package/name"
# O
docker-compose exec app composer require package/name
```

### **Ejecutar comandos de Artisan**

```bash
make artisan cmd="make:controller UserController"
# O
docker-compose exec app php artisan make:controller UserController
```

### **Ejecutar tests**

```bash
make test
# O
docker-compose exec app php artisan test
```

## 🔍 Troubleshooting

### **Problemas Comunes**

#### **1. Puerto 8000 ocupado**

```bash
# Cambiar puerto en docker-compose.yml
ports:
  - "8001:80"  # Cambiar 8000 por 8001
```

#### **2. Permisos de archivos**

```bash
make permissions
# O
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

#### **3. Limpiar caché**

```bash
make cache
# O
docker-compose exec app php artisan cache:clear
```

#### **4. Reconstruir contenedores**

```bash
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### **Logs y Debugging**

```bash
# Ver logs de todos los servicios
make logs

# Ver logs de un servicio específico
docker-compose logs app
docker-compose logs nginx
docker-compose logs db
```

## 📊 Monitoreo

### **Estado de contenedores**

```bash
make status
# O
docker-compose ps
```

### **Uso de recursos**

```bash
docker stats
```

## 🚀 Producción

### **Optimizar para producción**

```bash
make optimize
```

### **Configurar variables de entorno**

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com
```

## 🔒 Seguridad

### **Cambiar credenciales por defecto**

1. Editar `docker-compose.yml`
2. Cambiar `DB_PASSWORD` y `MYSQL_ROOT_PASSWORD`
3. Actualizar `.env`
4. Reconstruir contenedores

### **Configurar SSL**

Agregar certificados SSL en la configuración de Nginx.

## 📚 Recursos Adicionales

-   [Documentación de Docker](https://docs.docker.com/)
-   [Documentación de Laravel](https://laravel.com/docs)
-   [Docker Compose](https://docs.docker.com/compose/)

## 🤝 Contribuir

1. Fork el proyecto
2. Crear una rama para tu feature
3. Commit tus cambios
4. Push a la rama
5. Crear un Pull Request

---

**¡Disfruta desarrollando con Docker! 🐳**
