# ⚡ QUICK REFERENCE - Operaciones Diarias Post-Migración

## 🚀 INICIO RÁPIDO

```bash
# Ver estado actual
/root/apps/miapi-local/monitor.sh

# Ver logs en tiempo real
/root/apps/miapi-local/docker_ops.sh logs

# Ver contenedores
/root/apps/miapi-local/docker_ops.sh status
```

---

## 📋 COMANDOS MÁS USADOS

| Tarea | Comando |
|-------|---------|
| **Ver estado** | `docker ps -a` |
| **Logs en vivo** | `/root/apps/miapi-local/docker_ops.sh logs` |
| **Reiniciar todo** | `/root/apps/miapi-local/docker_ops.sh restart` |
| **Ejecutar migraciones** | `/root/apps/miapi-local/docker_ops.sh migrate` |
| **Limpiar caché** | `/root/apps/miapi-local/docker_ops.sh cache-clear` |
| **Ver errores Nginx** | `tail -f /var/log/nginx/api.nortedesantander.com.error.log` |
| **Acceder PHP shell** | `/root/apps/miapi-local/docker_ops.sh shell-php` |
| **Acceder MySQL** | `/root/apps/miapi-local/docker_ops.sh shell-mysql` |
| **Crear backup** | `/root/apps/miapi-local/docker_ops.sh backup` |

---

## 🔧 ALIASES (Agregar a ~/.bashrc)

```bash
# Copiar y pegar en terminal:
cat >> ~/.bashrc << 'EOF'

# Post-Migration Aliases
alias dops="/root/apps/miapi-local/docker_ops.sh"
alias dmon="/root/apps/miapi-local/monitor.sh"
alias dlogs="dops logs"
alias dstatus="dops status"
alias drestart="dops restart"
alias ddeploy="dops down && dops up && dops migrate"
alias dnginx="tail -f /var/log/nginx/api.nortedesantander.com.error.log"

EOF

# Recargar
source ~/.bashrc
```

Luego:
```bash
dmon          # Monitor
dlogs         # Logs
drestart      # Restart
ddeploy       # Full deployment
```

---

## 🐛 PROBLEMAS COMUNES

### ❌ "Filament sin estilos"

```bash
# Paso 1: Revisar proxy
curl http://127.0.0.1:8085/admin

# Paso 2: Ver logs
tail -20 /var/log/nginx/api.nortedesantander.com.error.log

# Paso 3: Reconstruir Vite (si es necesario)
su - dockeruser -c "cd /root/apps/miapi-local && docker compose -f docker-compose.prod.yml exec php npm run build"

# Paso 4: Recargar
/root/apps/miapi-local/docker_ops.sh restart
```

### ❌ "Base de datos no conecta"

```bash
# Revisar MySQL
/root/apps/miapi-local/docker_ops.sh shell-mysql

# O desde PHP:
/root/apps/miapi-local/docker_ops.sh tinker
# >>> DB::select('SELECT 1')
```

### ❌ "Login no funciona"

```bash
# Revisar sesiones
tail -50 /var/log/nginx/api.nortedesantander.com.error.log

# Ver permisos storage
su - dockeruser -c "cd /root/apps/miapi-local && docker compose -f docker-compose.prod.yml exec php ls -la /var/www/html/storage"

# Corregir permisos si es necesario
/root/apps/miapi-local/docker_ops.sh restart
```

### ❌ "Nginx error: 502 Bad Gateway"

```bash
# Verificar Docker
docker ps

# Si Docker está down
/root/apps/miapi-local/docker_ops.sh up

# Recargar Nginx
systemctl reload nginx
```

---

## 📊 MONITOREO BÁSICO

```bash
# Salud general
/root/apps/miapi-local/monitor.sh

# Espacio en disco
df -h

# RAM disponible
free -h

# Procesos principales
ps aux | grep -E "nginx|php|mysql|docker"
```

---

## 🔄 DEPLOYMENT (Nueva versión)

```bash
# 1. Bajar cambios
su - dockeruser -c "cd /root/apps/miapi-local && git pull"

# 2. Ejecutar migraciones si hay cambios en BD
/root/apps/miapi-local/docker_ops.sh migrate

# 3. Reconstruir caché
/root/apps/miapi-local/docker_ops.sh cache-rebuild

# 4. Restart si hay cambios en PHP
/root/apps/miapi-local/docker_ops.sh restart

# O todo de una vez:
/root/apps/miapi-local/docker_ops.sh down && \
/root/apps/miapi-local/docker_ops.sh up && \
/root/apps/miapi-local/docker_ops.sh migrate
```

---

## 💾 BACKUP & RESTORE

```bash
# Crear backup
/root/apps/miapi-local/docker_ops.sh backup

# Ver backups
ls -lh /root/backups/

# Restaurar backup
cd /root/backups
tar -xzf backup_YYYYMMDD_HHMMSS.tar.gz -C /
```

---

## 🔐 SSL / CERTIFICADOS

```bash
# Ver info del certificado
openssl x509 -in /etc/nginx/ssl/api.nortedesantander.com.crt -noout -text

# Probar conexión SSL
echo | openssl s_client -connect 127.0.0.1:443 -servername api.nortedesantander.com

# Renovar certificado (si es Let's Encrypt, manual)
# Para Cloudflare Origin: Se renueva automáticamente
```

---

## 🎯 TAREAS COMUNES

| Tarea | Comando |
|-------|---------|
| Crear usuario admin | `dops tinker` luego `php artisan tinker` |
| Resetear contraseña | `dops exec php php artisan tinker` |
| Ver base de datos | `dops shell-mysql` |
| Ejecutar seeder | `dops exec php php artisan db:seed` |
| Generar APP_KEY | `dops exec php php artisan key:generate` |
| Cache config | `dops cache-rebuild` |

---

## 📞 EMERGENCIA

Si todo está caído:

```bash
# 1. Revisar logs
tail -100 /var/log/nginx/api.nortedesantander.com.error.log
su - dockeruser -c "cd /root/apps/miapi-local && docker compose -f docker-compose.prod.yml logs"

# 2. Restart servicios
systemctl restart nginx
/root/apps/miapi-local/docker_ops.sh restart

# 3. Si no funciona, rollback
systemctl stop nginx
su - dockeruser -c "cd /root/apps/miapi-local && docker compose -f docker-compose.prod.yml down"

# 4. Verificar estado
/root/apps/miapi-local/monitor.sh
```

---

## ✅ VERIFICACIÓN RÁPIDA

```bash
# Ejecutar esto regularmente para validar todo está bien
echo "=== HEALTH CHECK ==="
/root/apps/miapi-local/monitor.sh
echo ""
echo "=== URLs ==="
echo "https://api.nortedesantander.com"
echo "https://api.nortedesantander.com/admin"
echo ""
echo "=== STORAGE USED ==="
df -h /root/apps/miapi-local
echo ""
echo "=== LAST ERRORS (Nginx) ==="
tail -5 /var/log/nginx/api.nortedesantander.com.error.log
```

---

## 📚 DOCUMENTACIÓN COMPLETA

- Full guide: `/root/apps/miapi-local/MIGRATION_FINAL_SUMMARY.md`
- Migration plan: `/root/apps/miapi-local/HESTIA_MIGRATION_PLAN.md`
- Current status: `/root/apps/miapi-local/MIGRATION_COMPLETED.md`

---

**Guardado en**: `/root/apps/miapi-local/QUICK_REFERENCE.md`
