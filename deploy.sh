#!/bin/bash
set -e
echo "Iniciando proceso de despliegue continuo en VPS..."
# 1. Navegar al directorio del proyecto
cd /root/apps/miapi-local

# 2. Descargar los últimos cambios de GitHub
echo "Descargando última versión de GitHub..."
git pull origin main

# 3. Asegurar la existencia de logs y permisos
mkdir -p logs
touch logs/laravel.log
chmod -R 775 src/storage src/bootstrap/cache logs

# 4. Levantar y compilar contenedores Docker con el compose de producción
echo "Reconstruyendo imágenes de Docker..."
docker compose -f docker-compose.prod.yml up -d --build
# 4.1 Corregir propietario de los archivos del proyecto para que coincidan
# con el usuario 'laravel' (uid=1000) dentro del contenedor PHP
echo "Corrigiendo permisos de archivos..."
docker compose -f docker-compose.prod.yml exec -T -u root php chown -R laravel:laravel /var/www/html

# 5. Ejecutar optimizaciones dentro del contenedor PHP
echo "Ejecutando optimizaciones de Laravel 12 y Filament..."
docker compose -f docker-compose.prod.yml exec -T php composer install --no-dev --optimize-autoloader

# Generar APP_KEY en producción si no existe
if ! grep -q "APP_KEY=base64" src/.env; then
echo "Generando APP_KEY de producción..."
docker compose -f docker-compose.prod.yml exec -T php php artisan key:generate
fi

# Colocar Laravel en modo mantenimiento temporal
docker compose -f docker-compose.prod.yml exec -T php php artisan down || true

# Ejecutar migraciones de base de datos de Filament y Laravel
echo "Ejecutando migraciones de Base de Datos..."
docker compose -f docker-compose.prod.yml exec -T php php artisan migrate --force

# Limpiar y recrear caché de Laravel 12
echo "Optimizando cachés..."
docker compose -f docker-compose.prod.yml exec -T php php artisan config:cache
docker compose -f docker-compose.prod.yml exec -T php php artisan route:cache
docker compose -f docker-compose.prod.yml exec -T php php artisan view:cache
docker compose -f docker-compose.prod.yml exec -T php php artisan filament:cache-components || true

# Reactivar la aplicación
docker compose -f docker-compose.prod.yml exec -T php php artisan up
echo "¡Despliegue completado con éxito con cero caída de servicio!"
