#!/bin/bash
set -euo pipefail

PROJECT_DIR="/root/apps/miapi-local"
COMPOSE_FILE="docker-compose.prod.yml"
DOCKER_USER="dockeruser"

run_as_dockeruser() {
  su - "$DOCKER_USER" -c "cd $PROJECT_DIR && $*"
}

echo "======================================================"
echo " Iniciando despliegue continuo en VPS"
echo " $(date '+%Y-%m-%d %H:%M:%S')"
echo "======================================================"

cd "$PROJECT_DIR"

PREVIOUS_COMMIT=$(git rev-parse HEAD)
echo "Commit previo (rollback target): $PREVIOUS_COMMIT"

echo ""
echo "[1/7] Sincronizando código con origin/main..."
if ! run_as_dockeruser "git diff --quiet" || ! run_as_dockeruser "git diff --cached --quiet"; then
  echo "  → Descartando cambios locales en archivos rastreados (el VPS debe reflejar GitHub)."
fi
run_as_dockeruser "git fetch origin main"
run_as_dockeruser "git reset --hard origin/main"

NEW_COMMIT=$(git rev-parse HEAD)
CHANGED_FILES=""
if [ "$PREVIOUS_COMMIT" != "$NEW_COMMIT" ]; then
  CHANGED_FILES=$(git diff --name-only "$PREVIOUS_COMMIT" "$NEW_COMMIT")
fi

mkdir -p logs
touch logs/laravel.log
chmod -R 775 src/storage src/bootstrap/cache logs

NEED_REBUILD=false
NEED_COMPOSER=false
NEED_NPM=false

if [ -z "$CHANGED_FILES" ]; then
  echo "Sin cambios nuevos respecto al commit anterior."
else
  if echo "$CHANGED_FILES" | grep -qE '^(dockerfiles/|docker-compose\.prod\.yml|nginx/)'; then
    NEED_REBUILD=true
  fi
  if echo "$CHANGED_FILES" | grep -qE '(composer\.(json|lock)|^src/composer\.(json|lock))'; then
    NEED_COMPOSER=true
  fi
  if echo "$CHANGED_FILES" | grep -qE '(package\.json|package-lock\.json|vite\.config\.(js|ts)|^src/resources/)'; then
    NEED_NPM=true
  fi
fi

# Primera vez o vendor incompleto
if [ ! -d src/vendor/filament/filament ]; then
  NEED_COMPOSER=true
fi

echo ""
if [ "$NEED_REBUILD" = true ]; then
  echo "[2/7] Reconstruyendo imágenes Docker (cambios en Docker/nginx)..."
  run_as_dockeruser "docker compose -f $COMPOSE_FILE up -d --build"
else
  echo "[2/7] Levantando contenedores (sin rebuild)..."
  run_as_dockeruser "docker compose -f $COMPOSE_FILE up -d"
fi

echo ""
echo "[3/7] Corrigiendo permisos de storage y cache..."
run_as_dockeruser "docker compose -f $COMPOSE_FILE exec -T -u root php chown -R laravel:laravel /var/www/html/storage /var/www/html/bootstrap/cache"

if [ "$NEED_COMPOSER" = true ]; then
  echo ""
  echo "[4/7] Instalando dependencias de Composer..."
  run_as_dockeruser "docker compose -f $COMPOSE_FILE exec -T php composer install --no-dev --optimize-autoloader --no-interaction"
else
  echo ""
  echo "[4/7] Composer: sin cambios en dependencias, omitido."
fi

if [ "$NEED_NPM" = true ]; then
  echo ""
  echo "[5/7] Compilando assets frontend..."
  run_as_dockeruser "docker compose -f $COMPOSE_FILE exec -T node npm ci --prefer-offline --no-audit" \
    || run_as_dockeruser "docker compose -f $COMPOSE_FILE exec -T node npm install --no-audit"
  run_as_dockeruser "docker compose -f $COMPOSE_FILE exec -T node npm run build"
else
  echo ""
  echo "[5/7] Frontend: sin cambios, npm build omitido."
fi

if ! grep -q "APP_KEY=base64" src/.env 2>/dev/null; then
  echo "Generando APP_KEY de producción..."
  run_as_dockeruser "docker compose -f $COMPOSE_FILE exec -T php php artisan key:generate --force"
fi

echo ""
echo "[6/7] Modo mantenimiento y migraciones..."
run_as_dockeruser "docker compose -f $COMPOSE_FILE exec -T php php artisan down --retry=15" || true

if ! run_as_dockeruser "docker compose -f $COMPOSE_FILE exec -T php php artisan migrate --force"; then
  echo "ERROR: Las migraciones fallaron. Iniciando rollback de código..."
  run_as_dockeruser "git reset --hard $PREVIOUS_COMMIT"
  run_as_dockeruser "docker compose -f $COMPOSE_FILE exec -T php php artisan up"
  echo "Rollback completado. Commit activo: $PREVIOUS_COMMIT"
  exit 1
fi

echo ""
echo "[7/7] Optimizando cachés..."
run_as_dockeruser "docker compose -f $COMPOSE_FILE exec -T php php artisan config:cache"
run_as_dockeruser "docker compose -f $COMPOSE_FILE exec -T php php artisan route:cache"
run_as_dockeruser "docker compose -f $COMPOSE_FILE exec -T php php artisan view:cache"
run_as_dockeruser "docker compose -f $COMPOSE_FILE exec -T php php artisan filament:cache-components" || true
run_as_dockeruser "docker compose -f $COMPOSE_FILE exec -T php php artisan up"

HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" --max-time 15 http://127.0.0.1:8085/ || echo "000")
if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "302" ]; then
  echo "Smoke test OK (HTTP $HTTP_CODE)"
else
  echo "ADVERTENCIA: smoke test devolvió HTTP $HTTP_CODE"
fi

echo ""
echo "======================================================"
echo " Despliegue completado con éxito"
echo " Commit desplegado: $(git rev-parse HEAD)"
echo "======================================================"
