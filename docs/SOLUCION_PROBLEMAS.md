# Guía de Solución de Problemas

## 🚨 Problemas Comunes y Soluciones

### 1. Error 500 - Server Error

#### Síntomas

-   Laravel devuelve error 500
-   Página en blanco o error genérico
-   Logs muestran errores de base de datos

#### Diagnóstico

```bash
# Ver logs de Laravel
docker exec monitorias_app cat /var/www/storage/logs/laravel.log

# Ver logs del contenedor
docker logs monitorias_app

# Verificar estado de contenedores
docker ps
```

#### Soluciones

**Problema: APP_KEY no configurada**

```bash
# Generar clave de aplicación
docker exec monitorias_app php artisan key:generate

# Limpiar caché
docker exec monitorias_app php artisan config:clear
docker exec monitorias_app php artisan cache:clear
```

**Problema: Conexión a base de datos**

```bash
# Verificar que MySQL esté ejecutándose
docker ps | grep monitorias_db

# Esperar a que MySQL se inicialice
sleep 30

# Verificar conexión
docker exec monitorias_app php artisan tinker
# En Tinker: DB::connection()->getPdo();
```

**Problema: Permisos de archivos**

```bash
# Corregir permisos
docker exec monitorias_app chown -R laravel:laravel /var/www
docker exec monitorias_app chmod -R 755 /var/www/storage
docker exec monitorias_app chmod -R 755 /var/www/bootstrap/cache
```

### 2. Error de Conexión a Base de Datos

#### Síntomas

-   `SQLSTATE[HY000] [2002] Connection refused`
-   `SQLSTATE[HY000] [1045] Access denied`
-   Error al ejecutar migraciones

#### Diagnóstico

```bash
# Verificar estado de MySQL
docker logs monitorias_db

# Verificar variables de entorno
docker exec monitorias_app php artisan config:show database

# Probar conexión directa
docker exec -it monitorias_db mysql -u monitorias -p
```

#### Soluciones

**Problema: MySQL no inicia**

```bash
# Verificar logs de MySQL
docker logs monitorias_db

# Reiniciar contenedor de base de datos
docker-compose restart db

# Si persiste, eliminar datos y recrear
docker-compose down -v
docker-compose up -d
```

**Problema: Credenciales incorrectas**

```bash
# Verificar archivo .env
docker exec monitorias_app cat /var/www/.env | grep DB_

# Recrear archivo .env si es necesario
# (Ver sección de instalación para contenido correcto)
```

**Problema: Puerto ocupado**

```bash
# Verificar puertos en uso
netstat -tulpn | grep :3310

# Cambiar puerto en docker-compose.yml
ports:
  - "3311:3306"  # Cambiar 3310 por 3311
```

### 3. Contenedores No Se Inician

#### Síntomas

-   `docker-compose up` falla
-   Contenedores se detienen inmediatamente
-   Errores de puertos o recursos

#### Diagnóstico

```bash
# Ver logs de docker-compose
docker-compose logs

# Verificar configuración
docker-compose config

# Verificar recursos disponibles
docker system df
```

#### Soluciones

**Problema: Puerto ocupado**

```bash
# Encontrar proceso que usa el puerto
lsof -i :8000
lsof -i :8080
lsof -i :3310

# Terminar proceso o cambiar puertos
kill -9 <PID>
```

**Problema: Memoria insuficiente**

```bash
# Verificar memoria disponible
free -h

# Aumentar memoria en Docker Desktop
# Settings > Resources > Memory: 4GB o más
```

**Problema: Espacio en disco**

```bash
# Verificar espacio disponible
df -h

# Limpiar Docker
docker system prune -a
```

### 4. Problemas de Permisos

#### Síntomas

-   `Permission denied` en logs
-   No se pueden crear archivos
-   Errores de escritura en storage

#### Soluciones

**Problema: Permisos de archivos**

```bash
# Corregir propietario
docker exec monitorias_app chown -R laravel:laravel /var/www

# Corregir permisos
docker exec monitorias_app chmod -R 755 /var/www/storage
docker exec monitorias_app chmod -R 755 /var/www/bootstrap/cache
```

**Problema: Permisos de volúmenes**

```bash
# En WSL2, verificar permisos del directorio
ls -la

# Si es necesario, cambiar permisos
chmod -R 755 .
```

### 5. Problemas de Red

#### Síntomas

-   Contenedores no pueden comunicarse
-   `Connection refused` entre servicios
-   DNS no resuelve nombres de contenedores

#### Diagnóstico

```bash
# Verificar redes Docker
docker network ls

# Inspeccionar red
docker network inspect monitorias_monitorias_network

# Probar conectividad entre contenedores
docker exec monitorias_app ping db
```

#### Soluciones

**Problema: Red no creada**

```bash
# Recrear red
docker-compose down
docker network prune
docker-compose up -d
```

**Problema: DNS no funciona**

```bash
# Verificar configuración de red
docker exec monitorias_app cat /etc/resolv.conf

# Reiniciar contenedores
docker-compose restart
```

### 6. Problemas de Caché

#### Síntomas

-   Cambios no se reflejan
-   Configuración antigua persiste
-   Comportamiento inesperado

#### Soluciones

**Limpiar todas las cachés**

```bash
# Limpiar caché de Laravel
docker exec monitorias_app php artisan config:clear
docker exec monitorias_app php artisan cache:clear
docker exec monitorias_app php artisan route:clear
docker exec monitorias_app php artisan view:clear

# Limpiar caché de Composer
docker exec monitorias_app composer dump-autoload
```

**Problema: Caché de configuración**

```bash
# Regenerar caché de configuración
docker exec monitorias_app php artisan config:cache

# O limpiar completamente
docker exec monitorias_app php artisan optimize:clear
```

### 7. Problemas de Migraciones

#### Síntomas

-   `Table already exists`
-   `Column already exists`
-   Errores de sintaxis SQL

#### Soluciones

**Problema: Migraciones duplicadas**

```bash
# Verificar estado de migraciones
docker exec monitorias_app php artisan migrate:status

# Revertir migraciones
docker exec monitorias_app php artisan migrate:rollback

# Ejecutar migraciones nuevamente
docker exec monitorias_app php artisan migrate
```

**Problema: Base de datos corrupta**

```bash
# Eliminar base de datos y recrear
docker-compose down -v
docker-compose up -d
docker exec monitorias_app php artisan migrate
```

### 8. Problemas de Autenticación

#### Síntomas

-   `Unauthenticated` en API
-   Tokens no funcionan
-   Login falla

#### Soluciones

**Problema: Tokens no válidos**

```bash
# Limpiar tokens antiguos
docker exec monitorias_app php artisan tinker
# En Tinker: DB::table('personal_access_tokens')->truncate();

# Regenerar clave de aplicación
docker exec monitorias_app php artisan key:generate
```

**Problema: Configuración de Sanctum**

```bash
# Verificar configuración
docker exec monitorias_app php artisan config:show sanctum

# Publicar configuración si es necesario
docker exec monitorias_app php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### 9. Problemas de Rendimiento

#### Síntomas

-   Respuestas lentas
-   Timeouts
-   Alto uso de CPU/memoria

#### Soluciones

**Optimizar Laravel**

```bash
# Optimizar autoloader
docker exec monitorias_app composer install --optimize-autoloader --no-dev

# Cachear configuración
docker exec monitorias_app php artisan config:cache
docker exec monitorias_app php artisan route:cache
```

**Optimizar Docker**

```bash
# Limpiar recursos no utilizados
docker system prune -a

# Aumentar recursos en Docker Desktop
# Settings > Resources > Memory: 4GB, CPU: 2 cores
```

### 10. Problemas Específicos de WSL2

#### Síntomas

-   Rendimiento lento
-   Problemas de sincronización de archivos
-   Errores de permisos

#### Soluciones

**Problema: Rendimiento lento**

```bash
# Mover proyecto a directorio de WSL2
# En lugar de /mnt/c/, usar /home/usuario/

# Configurar .wslconfig en Windows
# [wsl2]
# memory=4GB
# processors=2
```

**Problema: Sincronización de archivos**

```bash
# Usar volúmenes Docker en lugar de bind mounts
# Modificar docker-compose.yml para usar volúmenes nombrados
```

## 🔧 Herramientas de Diagnóstico

### Comandos Útiles

```bash
# Verificar estado general
docker ps
docker-compose ps

# Ver logs en tiempo real
docker-compose logs -f

# Ver logs de un servicio específico
docker-compose logs app

# Verificar recursos del sistema
docker system df
docker stats

# Verificar configuración
docker-compose config

# Verificar redes
docker network ls
docker network inspect monitorias_monitorias_network
```

### Scripts de Diagnóstico

**Script: check-health.sh**

```bash
#!/bin/bash
echo "=== Docker Health Check ==="
echo "Containers:"
docker ps
echo ""
echo "Networks:"
docker network ls
echo ""
echo "Volumes:"
docker volume ls
echo ""
echo "Resources:"
docker system df
```

**Script: laravel-check.sh**

```bash
#!/bin/bash
echo "=== Laravel Health Check ==="
echo "Environment:"
docker exec monitorias_app php artisan env
echo ""
echo "Database Connection:"
docker exec monitorias_app php artisan tinker --execute="echo DB::connection()->getPdo() ? 'OK' : 'FAILED';"
echo ""
echo "Routes:"
docker exec monitorias_app php artisan route:list --compact
```

## 📞 Contacto y Soporte

### Información para Reportar Problemas

Cuando reportes un problema, incluye:

1. **Sistema operativo y versión**
2. **Versión de Docker**
3. **Comando que causó el error**
4. **Logs completos**
5. **Pasos para reproducir**

### Comandos para Obtener Información

```bash
# Información del sistema
uname -a
docker --version
docker-compose --version

# Estado de contenedores
docker ps -a

# Logs completos
docker-compose logs

# Configuración
docker-compose config
```

## 🚀 Recuperación Completa

Si todo falla, aquí está el proceso de recuperación completa:

```bash
# 1. Detener todo
docker-compose down -v

# 2. Limpiar Docker
docker system prune -a

# 3. Reconstruir desde cero
docker-compose up -d --build

# 4. Esperar a que MySQL se inicialice
sleep 30

# 5. Configurar Laravel
docker exec monitorias_app php artisan key:generate
docker exec monitorias_app php artisan migrate

# 6. Verificar
curl http://localhost:8000
```

## 📚 Recursos Adicionales

-   [Docker Troubleshooting](https://docs.docker.com/engine/troubleshooting/)
-   [Laravel Troubleshooting](https://laravel.com/docs/troubleshooting)
-   [WSL2 Troubleshooting](https://docs.microsoft.com/en-us/windows/wsl/troubleshooting)
-   [MySQL Error Reference](https://dev.mysql.com/doc/refman/8.0/en/error-reference.html)
