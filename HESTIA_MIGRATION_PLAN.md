# 🏗️ PLAN DE MIGRACIÓN: HestiaCP → Nginx Manual + Docker

## DIAGNÓSTICO ACTUAL

### ❌ Problemas Identificados:
1. **Conflicto de usuarios**: Contenedores como `root` vs HestiaCP como `nanoContabo`
2. **Nginx duplicado**: HestiaCP Nginx + Docker Nginx compitiendo
3. **Permisos incompatibles**: Sudo/permisos cruzados
4. **Proxy mal configurado**: HestiaCP proxy no optimizado para Docker
5. **Complejidad innecesaria**: Capas de abstracción HestiaCP en producción

### ✅ Lo que tienes funcionando:
- Certificados SSL válidos a 15 años (Cloudflare Origin)
- 3 dominios configurados en HestiaCP:
  - `nortedesantander.com` (principal)
  - `api.nortedesantander.com` (Docker API + Filament)
  - `tienda.nortedesantander.com` (¿backend?)
- Docker Compose configurado en `/root/apps/miapi-local/`

---

## 📋 FASE 1: PRE-MIGRACIÓN (BACKUP & PLANNING)

### Paso 1: Extraer y Backup de Certificados SSL

```bash
# Crear directorio de backup
mkdir -p /root/ssl_backup

# Extraer certificados de HestiaCP
cp /usr/local/hestia/data/users/nanoContabo/ssl/api.nortedesantander.com.crt /root/ssl_backup/
cp /usr/local/hestia/data/users/nanoContabo/ssl/api.nortedesantander.com.key /root/ssl_backup/
cp /usr/local/hestia/data/users/nanoContabo/ssl/api.nortedesantander.com.pem /root/ssl_backup/

# Verificar certificado
openssl x509 -in /root/ssl_backup/api.nortedesantander.com.crt -text -noout | grep -A 2 "Subject\|Issuer\|Valid\|Not"

# Backup de todas las config de nanoContabo
tar -czf /root/hestia_backup_nanoContabo_$(date +%Y%m%d).tar.gz \
  /usr/local/hestia/data/users/nanoContabo/ \
  /home/nanoContabo/ 2>/dev/null
```

### Paso 2: Documentar Estado Actual

```bash
# Documentar configuración de HestiaCP
cat /usr/local/hestia/data/users/nanoContabo/web.conf > /root/HESTIA_WEB_CONFIG_BACKUP.txt

# Documentar usuarios del sistema
id nanoContabo > /root/USER_INFO.txt
id root >> /root/USER_INFO.txt
docker ps -a >> /root/DOCKER_INFO.txt
```

### Paso 3: Planificación de Downtime

**Ventana recomendada**: Fuera de horario (ej: 00:00-02:00 UTC)

---

## 🔧 FASE 2: CONFIGURACIÓN DE NGINX MANUAL

### Paso 1: Instalar Nginx (si no está)

```bash
# Nginx probablemente ya existe, pero vamos a asegurar
apt-get update
apt-get install -y nginx nginx-extras

# Verificar versión
nginx -v
```

### Paso 2: Crear Estructura de Directorios

```bash
# Crear directorios de SSL
sudo mkdir -p /etc/nginx/ssl
sudo mkdir -p /etc/nginx/conf.d
sudo mkdir -p /var/www/html

# Mover certificados a ubicación estándar
sudo cp /root/ssl_backup/api.nortedesantander.com.crt /etc/nginx/ssl/
sudo cp /root/ssl_backup/api.nortedesantander.com.key /etc/nginx/ssl/
sudo chmod 400 /etc/nginx/ssl/*.key
sudo chown root:root /etc/nginx/ssl/*
```

### Paso 3: Configurar Nginx como Reverse Proxy

**Archivo: `/etc/nginx/sites-available/api-docker`**

```nginx
upstream docker_api {
    least_conn;
    server 127.0.0.1:8085 max_fails=3 fail_timeout=30s;
}

# HTTP → HTTPS redirect
server {
    listen 80;
    listen [::]:80;
    server_name api.nortedesantander.com;
    
    location /.well-known/acme-challenge/ {
        root /var/www/certbot;
    }
    
    location / {
        return 301 https://$server_name$request_uri;
    }
}

# HTTPS proxy
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name api.nortedesantander.com;

    # SSL Configuration (Cloudflare Origin Cert)
    ssl_certificate /etc/nginx/ssl/api.nortedesantander.com.crt;
    ssl_certificate_key /etc/nginx/ssl/api.nortedesantander.com.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    # Security Headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Logging
    access_log /var/log/nginx/api.nortedesantander.com.access.log combined buffer=32k flush=5s;
    error_log /var/log/nginx/api.nortedesantander.com.error.log warn;

    # Proxy headers - CRÍTICO para Laravel/Filament
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_set_header X-Forwarded-Host $server_name;
    proxy_set_header Connection "";
    proxy_http_version 1.1;

    # Timeouts
    proxy_connect_timeout 60s;
    proxy_send_timeout 60s;
    proxy_read_timeout 60s;

    # Reverse proxy configuration
    location / {
        proxy_pass http://docker_api;
    }

    # Static assets caching (mejor performance)
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        proxy_pass http://docker_api;
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # Filament admin - no cache
    location /admin {
        proxy_pass http://docker_api;
        proxy_cache_bypass 1;
        add_header Cache-Control "no-cache, no-store, must-revalidate";
    }
}
```

### Paso 4: Habilitar el Sitio

```bash
# Symlink a sites-enabled
sudo ln -s /etc/nginx/sites-available/api-docker /etc/nginx/sites-enabled/api-docker

# Remover config por defecto si existe
sudo rm -f /etc/nginx/sites-enabled/default

# Validar sintaxis
sudo nginx -t

# Recargar (sin downtime)
sudo systemctl reload nginx
```

---

## 🐳 FASE 3: MIGRACIÓN DE DOCKER

### Paso 1: Resolver Conflicto de Usuarios

```bash
# Ver propietario actual de archivos
ls -la /root/apps/miapi-local/

# Los contenedores están creados como root (PID=0), esto es normal en producción
# pero queremos mejor práctica: crear usuario docker
sudo useradd -m -s /bin/bash dockeruser

# Agregar dockeruser al grupo docker
sudo usermod -aG docker dockeruser

# Cambiar permisos de directorio de proyecto
sudo chown -R dockeruser:dockeruser /root/apps/miapi-local/
```

### Paso 2: Ajustar docker-compose.prod.yml

**Cambios necesarios:**

```yaml
# En el archivo docker-compose.prod.yml, actualizar:

services:
  php:
    # Agregar user non-root
    user: "1000:1000"  # UID:GID de dockeruser
    # Rest igual...

  artisan:
    user: "1000:1000"
    # Rest igual...
```

### Paso 3: Reiniciar Contenedores

```bash
# Como dockeruser
sudo -u dockeruser docker-compose -f /root/apps/miapi-local/docker-compose.prod.yml down
sudo -u dockeruser docker-compose -f /root/apps/miapi-local/docker-compose.prod.yml up -d

# Verificar
docker ps -a
```

---

## 🗑️ FASE 4: DESINSTALAR HESTIACP

### ⚠️ ADVERTENCIA CRÍTICA
**Antes de ejecutar, asegurate de:**
- ✅ Todos los certificados están en `/etc/nginx/ssl`
- ✅ Nginx vanilla está corriendo y respondiendo
- ✅ Dominios funcionan con nuevo Nginx
- ✅ Docker está actualizado con permisos correctos

### Paso 1: Verificar que TODO está migrando correctamente

```bash
# Test de conectividad
curl -I https://api.nortedesantander.com/
# Debe retornar 200 OK y estilos cargando

# Test de login Filament
curl -I https://api.nortedesantander.com/admin
# Debe retornar 200 OK
```

### Paso 2: Backup Final

```bash
# Backup completo del sistema
tar -czf /root/full_system_backup_$(date +%Y%m%d_%H%M%S).tar.gz \
  /home/nanoContabo \
  /usr/local/hestia 2>/dev/null

# Guardar en ubicación segura
mkdir -p /root/backups
cp /root/full_system_backup_*.tar.gz /root/backups/
```

### Paso 3: Remover HestiaCP

```bash
# Parar servicio HestiaCP
sudo systemctl stop hestia-daemon 2>/dev/null || true
sudo systemctl stop hestia-web 2>/dev/null || true

# OPCIÓN A: Desinstalación completa (más limpia)
# Desde usuario hestia o root:
/usr/local/hestia/bin/v-delete-user nanoContabo

# OPCIÓN B: Solo remover servicios
sudo systemctl disable hestia-daemon
sudo systemctl disable hestia-web
sudo systemctl disable php-fpm

# Purgar paquetes HestiaCP (si está en apt)
sudo apt-get remove hestia hestia-php --purge -y 2>/dev/null || true

# Limpiar directorios huérfanos
sudo rm -rf /usr/local/hestia/data/users/nanoContabo/
sudo rm -rf /home/nanoContabo/ (CUIDADO: verificar si tiene datos antes)
```

---

## ✅ FASE 5: VALIDACIÓN POST-MIGRACIÓN

### Test 1: Nginx Proxy Funciona
```bash
# Verificar headers correctos desde Docker
curl -v https://api.nortedesantander.com/ 2>&1 | grep -i "server\|x-powered\|laravel"
# Debe mostrar que es Django/Laravel, no HestiaCP
```

### Test 2: Filament Admin
```bash
# Acceder a https://api.nortedesantander.com/admin desde navegador
# ✓ CSS/Tailwind cargando
# ✓ Login form visible
# ✓ Inputs aceptando credenciales
# ✓ Sesiones funcionando (cookies visibles en DevTools)
```

### Test 3: SSL Válido
```bash
# Verificar certificado
openssl s_client -connect api.nortedesantander.com:443 -servername api.nortedesantander.com 2>/dev/null | grep -A 5 "Subject\|Issuer"

# Debe mostrar Cloudflare Origin Cert válido a 15 años
```

### Test 4: Headers de Seguridad
```bash
curl -I https://api.nortedesantander.com/ | grep -i "strict-transport\|x-content\|x-frame"

# Debe retornar todos los headers de seguridad
```

### Test 5: Logs Nginx
```bash
# Revisar logs
tail -30 /var/log/nginx/api.nortedesantander.com.access.log
tail -30 /var/log/nginx/api.nortedesantander.com.error.log

# No deben haber errores críticos
```

---

## 🔄 ROLLBACK PLAN (Si algo falla)

### Opción 1: Revertir Nginx a HestiaCP
```bash
# Desactivar nginx vanilla
sudo systemctl stop nginx
sudo systemctl disable nginx

# Reactivar HestiaCP
sudo systemctl start hestia-daemon
sudo systemctl start hestia-web

# HestiaCP debería recuperarse automáticamente
```

### Opción 2: Recuperar desde Backup
```bash
# Si HestiaCP es necesario temporalmente
cd /root/backups
tar -xzf full_system_backup_*.tar.gz -C /

# Restartar servicios
sudo systemctl restart hestia-daemon
```

---

## 📊 COMPARATIVA: HestiaCP vs Nginx Manual

| Aspecto | HestiaCP | Nginx Manual |
|---------|----------|--------------|
| **Complejidad** | Alta | Baja |
| **Overhead de recursos** | ~15-20% (panel web) | Mínimo |
| **Control** | GUI (abstracto) | Directo (files) |
| **Escalabilidad** | Limitada | Excelente |
| **Docker integration** | Complicada | Nativa |
| **Mantenimiento** | Panel web | Scripts/Ansible |
| **Curva aprendizaje** | Moderada | Baja (si sabes Nginx) |
| **Costo operativo** | Licencia HestiaCP | Gratis (FOSS) |

---

## 🚀 PRÓXIMOS PASOS DESPUÉS DE MIGRACIÓN

1. **Automatizar backups**:
   ```bash
   # Cron diario a las 02:00
   0 2 * * * tar -czf /root/backups/docker_backup_$(date +\%Y\%m\%d).tar.gz /root/apps/miapi-local/
   ```

2. **Monitoreo de Nginx**:
   ```bash
   # Instalar certbot para renovación automática (si cambias a Let's Encrypt después)
   sudo apt-get install certbot python3-certbot-nginx
   ```

3. **CI/CD simplificado**:
   ```bash
   # Crear script de deployment
   #!/bin/bash
   cd /root/apps/miapi-local
   docker-compose -f docker-compose.prod.yml pull
   docker-compose -f docker-compose.prod.yml up -d
   docker-compose -f docker-compose.prod.yml exec php php artisan migrate --force
   ```

4. **Logging centralizado**:
   ```bash
   # Agregar a docker-compose.prod.yml logging driver
   logging:
     driver: "json-file"
     options:
       max-size: "10m"
       max-file: "3"
   ```

---

## 📞 CONTACTO DE SOPORTE

Si algo falla:
1. Revisar logs: `/var/log/nginx/api.nortedesantander.com.error.log`
2. Verificar Docker: `docker-compose -f docker-compose.prod.yml logs php`
3. Probar conectividad: `curl -v https://api.nortedesantander.com/admin`
4. Validar certificado: `openssl x509 -in /etc/nginx/ssl/api.nortedesantander.com.crt -text`
