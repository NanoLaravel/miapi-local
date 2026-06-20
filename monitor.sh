#!/bin/bash
# 📊 MONITORING SCRIPT - Post-HestiaCP Migration
# Monitoreo de servicios y alertas

LOG_FILE="/var/log/nginx/migration_monitor.log"
ERROR_THRESHOLD=5

# Funciones
log_check() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE"
}

check_service() {
    local service=$1
    local port=$2
    
    if systemctl is-active --quiet "$service"; then
        echo "✅ $service: Corriendo"
        log_check "$service: OK"
        return 0
    else
        echo "❌ $service: STOPPED"
        log_check "$service: FAILED"
        return 1
    fi
}

check_port() {
    local port=$1
    local service=$2
    
    if netstat -tlnp 2>/dev/null | grep -q ":$port "; then
        echo "✅ Puerto $port ($service): Escuchando"
        log_check "Port $port: OK"
        return 0
    else
        echo "❌ Puerto $port ($service): NO escuchando"
        log_check "Port $port: FAILED"
        return 1
    fi
}

check_docker_container() {
    local container=$1
    
    if docker ps --filter "name=$container" --format "{{.Status}}" | grep -q "Up"; then
        echo "✅ Docker $container: Corriendo"
        log_check "$container: OK"
        return 0
    else
        echo "❌ Docker $container: STOPPED"
        log_check "$container: FAILED"
        return 1
    fi
}

check_disk_space() {
    local usage=$(df /root/apps/miapi-local | tail -1 | awk '{print $5}' | sed 's/%//')
    if [ "$usage" -lt 80 ]; then
        echo "✅ Espacio en disco: ${usage}% usado"
        log_check "Disk space: ${usage}% OK"
        return 0
    else
        echo "⚠️  Espacio en disco: ${usage}% usado (CRÍTICO)"
        log_check "Disk space: ${usage}% CRITICAL"
        return 1
    fi
}

check_error_logs() {
    local errors=$(tail -100 /var/log/nginx/api.nortedesantander.com.error.log 2>/dev/null | grep -i "error\|critical\|fatal" | wc -l)
    if [ "$errors" -lt "$ERROR_THRESHOLD" ]; then
        echo "✅ Errores en Nginx: $errors"
        log_check "Nginx errors: $errors OK"
        return 0
    else
        echo "❌ Errores en Nginx: $errors (CRÍTICO)"
        log_check "Nginx errors: $errors CRITICAL"
        return 1
    fi
}

# MAIN
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📊 SYSTEM HEALTH CHECK - $(date '+%Y-%m-%d %H:%M:%S')"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

FAILED=0

echo "🔍 SERVICIOS:"
check_service "nginx" "443" || ((FAILED++))
echo ""

echo "🔍 PUERTOS:"
check_port "80" "HTTP" || ((FAILED++))
check_port "443" "HTTPS" || ((FAILED++))
check_port "8085" "Docker Nginx" || ((FAILED++))
check_port "8090" "PHPMyAdmin" || ((FAILED++))
check_port "33061" "MySQL" || ((FAILED++))
echo ""

echo "🐳 DOCKER CONTAINERS:"
check_docker_container "laravel_server" || ((FAILED++))
check_docker_container "laravel_php" || ((FAILED++))
check_docker_container "laravel_mysql" || ((FAILED++))
echo ""

echo "💾 SISTEMA:"
check_disk_space || ((FAILED++))
echo ""

echo "📋 LOGS:"
check_error_logs || ((FAILED++))
echo ""

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
if [ "$FAILED" -eq 0 ]; then
    echo "✅ TODO ESTÁ BIEN"
else
    echo "❌ $FAILED PROBLEMAS ENCONTRADOS"
fi
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

exit "$FAILED"
