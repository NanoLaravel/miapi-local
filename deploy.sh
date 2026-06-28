#!/bin/bash
set -e

echo "======================================================"
echo " Iniciando despliegue continuo en VPS"
echo "======================================================"

# 1. Navegar al directorio del proyecto
cd /root/apps/miapi-local

# Guardar el commit actual para rollback en caso de fallo
PREVIOUS_COMMIT=$(git rev-parse HEAD)
echo "Commit previo (rollback target): $PREVIOUS_COMMIT"

# 2. Descargar los últimos cambios de GitHub
echo ""
echo "[1/6] Descargando última versión de GitHub..."
git pull origin main

# 3. Asegurar la existencia de logs y permisos
mkdir -p logs
touch logs/laravel.log
chmod -R 775 src/storage src/bootstrap/cache logs

# 4. Levantar y compilar contenedores Docker con el compose de producción
echo ""
echo "[2/6] Reconstruyendo imágenes de Docker..."
docker compose -f docker-compose.prod.yml up -d --build

# 5. Instalar dependencias de Composer (sin dev)
echo ""
echo "[3/6] Instalando dependencias de Composer..."
docker compose -f docker-compose.prod.yml exec -T php composer install --no-dev --optimize-autoloader

# Generar APP_KEY en producción si no existe
if ! grep -q "APP_KEY=base64" src/.env; then
  echo "Generando APP_KEY de producción..."
  docker compose -f docker-compose.prod.yml exec -T php php artisan key:generate
fi

# Colocar Laravel en modo mantenimiento temporal
echo ""
echo "[4/6] Activando modo mantenimiento..."
docker compose -f docker-compose.prod.yml exec -T php php artisan down --retry=15 || true

# 6. Ejecutar migraciones — con manejo de error y rollback automático
echo ""
echo "[5/6] Ejecutando migraciones de Base de Datos..."
if ! docker compose -f docker-compose.prod.yml exec -T php php artisan migrate --force; then
  echo "ERROR: Las migraciones fallaron. Iniciando rollback de código..."
  git reset --hard "$PREVIOUS_COMMIT"
  docker compose -f docker-compose.prod.yml exec -T php php artisan up
  echo "Rollback completado. La aplicación sigue corriendo con el commit: $PREVIOUS_COMMIT"
  exit 1
fi

# 7. Limpiar y recrear caché de Laravel 12
echo ""
echo "[6/6] Optimizando cachés..."
docker compose -f docker-compose.prod.yml exec -T php php artisan config:cache
docker compose -f docker-compose.prod.yml exec -T php php artisan route:cache
docker compose -f docker-compose.prod.yml exec -T php php artisan view:cache
docker compose -f docker-compose.prod.yml exec -T php php artisan filament:cache-components || true

# Reactivar la aplicación
docker compose -f docker-compose.prod.yml exec -T php php artisan up

echo ""
echo "======================================================"
echo " ¡Despliegue completado con éxito!"
echo " Commit desplegado: $(git rev-parse HEAD)"
echo "======================================================"
