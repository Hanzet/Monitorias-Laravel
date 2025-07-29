# Makefile para Laravel con Docker

.PHONY: help build up down restart logs shell composer artisan migrate seed fresh test

help: ## Mostrar esta ayuda
	@echo "Comandos disponibles:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

build: ## Construir las imágenes de Docker
	docker-compose build

up: ## Levantar los contenedores
	docker-compose up -d

down: ## Detener los contenedores
	docker-compose down

restart: ## Reiniciar los contenedores
	docker-compose restart

logs: ## Ver logs de los contenedores
	docker-compose logs -f

shell: ## Acceder al shell del contenedor de la aplicación
	docker-compose exec app bash

composer: ## Ejecutar comandos de Composer (uso: make composer cmd="install")
	docker-compose exec app composer $(cmd)

artisan: ## Ejecutar comandos de Artisan (uso: make artisan cmd="migrate")
	docker-compose exec app php artisan $(cmd)

migrate: ## Ejecutar migraciones
	docker-compose exec app php artisan migrate

seed: ## Ejecutar seeders
	docker-compose exec app php artisan db:seed

fresh: ## Refrescar base de datos (migrate:fresh + seed)
	docker-compose exec app php artisan migrate:fresh --seed

test: ## Ejecutar tests
	docker-compose exec app php artisan test

init: ## Inicializar el proyecto (primera vez)
	docker-compose up -d
	sleep 10
	docker-compose exec app bash /var/www/docker/init.sh

status: ## Ver estado de los contenedores
	docker-compose ps

clean: ## Limpiar contenedores, redes y volúmenes no utilizados
	docker system prune -f
	docker volume prune -f

install: ## Instalar dependencias de Composer
	docker-compose exec app composer install

update: ## Actualizar dependencias de Composer
	docker-compose exec app composer update

cache: ## Limpiar caché de Laravel
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear

optimize: ## Optimizar Laravel para producción
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:cache

permissions: ## Configurar permisos
	docker-compose exec app chmod -R 775 storage bootstrap/cache
	docker-compose exec app chown -R www-data:www-data storage bootstrap/cache 