#!/bin/bash

# Script de inicialización para Laravel en Docker

echo "🚀 Iniciando configuración de Laravel en Docker..."

# Generar clave de aplicación
echo "📝 Generando clave de aplicación..."
php artisan key:generate

# Ejecutar migraciones
echo "🗄️ Ejecutando migraciones..."
php artisan migrate --force

# Ejecutar seeders
echo "🌱 Ejecutando seeders..."
php artisan db:seed --force

# Limpiar caché
echo "🧹 Limpiando caché..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimizar para producción
echo "⚡ Optimizando aplicación..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Configurar permisos
echo "🔐 Configurando permisos..."
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

echo "✅ Configuración completada!"
echo "🌐 Tu aplicación está disponible en: http://localhost:8000"
echo "🗄️ phpMyAdmin está disponible en: http://localhost:8080" 