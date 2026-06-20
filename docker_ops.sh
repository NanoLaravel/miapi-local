#!/bin/bash
# 🚀 DOCKER OPERATIONS SCRIPT - Post-HestiaCP Migration
# Uso: ./docker_ops.sh [command]

set -e

DOCKER_USER="dockeruser"
PROJECT_DIR="/root/apps/miapi-local"
DOCKER_COMPOSE_FILE="$PROJECT_DIR/docker-compose.prod.yml"

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Funciones
log_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Ejecutar comando como dockeruser
run_as_docker() {
    su - $DOCKER_USER -c "cd $PROJECT_DIR && $1"
}

# COMMANDS
case "${1:-help}" in
    up)
        log_info "🚀 Iniciando contenedores..."
        run_as_docker "docker compose -f docker-compose.prod.yml up -d"
        log_success "Contenedores iniciados"
        $0 status
        ;;
    down)
        log_info "🛑 Deteniendo contenedores..."
        run_as_docker "docker compose -f docker-compose.prod.yml down"
        log_success "Contenedores detenidos"
        ;;
    restart)
        log_info "🔄 Reiniciando contenedores..."
        run_as_docker "docker compose -f docker-compose.prod.yml restart"
        log_success "Contenedores reiniciados"
        $0 status
        ;;
    logs)
        log_info "📋 Mostrando logs en tiempo real (Ctrl+C para salir)..."
        run_as_docker "docker compose -f docker-compose.prod.yml logs -f"
        ;;
    status)
        log_info "📊 Estado de contenedores:"
        docker ps --filter "label!=com.docker.swarm.node" --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
        ;;
    migrate)
        log_info "🔄 Ejecutando migraciones..."
        run_as_docker "docker compose -f docker-compose.prod.yml exec php php artisan migrate --force"
        log_success "Migraciones completadas"
        ;;
    cache-clear)
        log_info "🗑️  Limpiando caché..."
        run_as_docker "docker compose -f docker-compose.prod.yml exec php php artisan cache:clear"
        log_success "Caché limpiado"
        ;;
    cache-rebuild)
        log_info "🔨 Reconstruyendo configuración..."
        run_as_docker "docker compose -f docker-compose.prod.yml exec php php artisan config:cache"
        log_success "Configuración cacheada"
        ;;
    tinker)
        log_info "🐚 Abriendo Tinker shell..."
        run_as_docker "docker compose -f docker-compose.prod.yml exec php php artisan tinker"
        ;;
    shell-php)
        log_info "🐚 Shell en contenedor PHP..."
        run_as_docker "docker compose -f docker-compose.prod.yml exec php /bin/bash"
        ;;
    shell-mysql)
        log_info "🗄️  Shell en MySQL..."
        run_as_docker "docker compose -f docker-compose.prod.yml exec mysql mysql -u\${DB_USERNAME} -p\${DB_PASSWORD} \${DB_DATABASE}"
        ;;
    backup)
        log_info "💾 Creando backup..."
        BACKUP_FILE="/root/backups/backup_$(date +%Y%m%d_%H%M%S).tar.gz"
        mkdir -p /root/backups
        tar -czf "$BACKUP_FILE" "$PROJECT_DIR/"
        log_success "Backup creado: $BACKUP_FILE"
        ls -lh "$BACKUP_FILE"
        ;;
    nginx-logs)
        log_info "📋 Logs de Nginx (últimas 30 líneas):"
        tail -30 /var/log/nginx/api.nortedesantander.com.error.log 2>/dev/null || log_warning "Log no encontrado"
        ;;
    nginx-reload)
        log_info "🔄 Recargando Nginx..."
        systemctl reload nginx
        log_success "Nginx recargado"
        ;;
    ssl-info)
        log_info "🔐 Información del certificado SSL:"
        openssl x509 -in /etc/nginx/ssl/api.nortedesantander.com.crt -noout -text | grep -i "subject\|issuer\|validity\|not"
        ;;
    test)
        log_info "🧪 Ejecutando tests de conectividad..."
        log_info "1️⃣  HTTP → HTTPS redirect:"
        curl -I http://127.0.0.1/ 2>&1 | head -1
        
        log_info "2️⃣  SSL connection:"
        openssl s_client -connect 127.0.0.1:443 -showcerts -servername api.nortedesantander.com </dev/null 2>&1 | head -5
        
        log_info "3️⃣  Docker Nginx response (local):"
        curl -s http://127.0.0.1:8085/ | head -c 100
        
        log_success "Tests completados"
        ;;
    help|*)
        cat << EOF
${BLUE}🐳 DOCKER OPERATIONS SCRIPT${NC}

Uso: $0 [command]

${BLUE}Contenedores:${NC}
  up              Iniciar contenedores
  down            Detener contenedores
  restart         Reiniciar contenedores
  status          Ver estado de contenedores
  logs            Ver logs en tiempo real (Ctrl+C para salir)

${BLUE}Laravel:${NC}
  migrate         Ejecutar migraciones
  cache-clear     Limpiar caché
  cache-rebuild   Reconfigurar y cachear config
  tinker          Abrir Laravel Tinker shell

${BLUE}Shells:${NC}
  shell-php       Acceso SSH al contenedor PHP
  shell-mysql     Acceso MySQL interactivo

${BLUE}Sistema:${NC}
  backup          Crear backup del proyecto
  nginx-logs      Ver logs de Nginx
  nginx-reload    Recargar Nginx sin downtime
  ssl-info        Mostrar info del certificado SSL
  test            Tests de conectividad

${BLUE}Help:${NC}
  help            Mostrar esta ayuda

${BLUE}Ejemplos:${NC}
  $0 up
  $0 logs
  $0 migrate
  $0 backup

EOF
        ;;
esac
