# 🚀 CHECKLIST DE DESPLIEGUE - NORTEDESANTANDER.COM

## Estado Actual de Correcciones ✅
- [x] Nginx configurado para aceptar cualquier dominio (`server_name _`)
- [x] Sanctum reconfigurado para `api.nortedesantander.com`
- [x] SESSION_DRIVER cambiado a `database` (más seguro)
- [x] LOG_LEVEL en `warning` (producción)
- [x] SESSION_DOMAIN en `.nortedesantander.com` (funciona en subdominio)

---

## ⚙️ PASO 1: Reiniciar Contenedores y Migraciones

```bash
# En el VPS, desde /root/apps/miapi-local/
docker-compose -f docker-compose.prod.yml down
docker-compose -f docker-compose.prod.yml up -d

# Ejecutar migraciones (la tabla de sesiones ya existe)
docker-compose -f docker-compose.prod.yml exec php php artisan migrate --force
```

---

## 🔧 PASO 2: Validaciones en Cloudflare Dashboard

### SSL/TLS Settings
- [ ] **SSL/TLS Encryption Mode**: Verificar que está en **"Full"** (no Flexible)
- [ ] **Origin Server**: Debe estar accesible en `127.0.0.1:8085` (solo localhost)
- [ ] **Certificate Authority**: Cloudflare Origin Certificate debe estar instalado

### Caching & Performance
- [ ] **Caching Level**: Establecer en **"Cache Everything"** SOLO para:
  - `/storage/*` → 1 mes
  - `/vendor/*` → 1 mes
  - `/public/css/*`, `/public/js/*` → 1 semana
- [ ] **Browser Cache TTL**: 30 minutos (Filament necesita actualizar assets)

### Page Rules (Importante para Filament)
Crear rules para:
```
URL: api.nortedesantander.com/admin*
→ Cache Level: Bypass
→ Browser Cache TTL: 30 minutes
→ Security Level: Medium
```

### Headers & Security
- [ ] **X-Frame-Options**: Permitir (Filament usa iframes)
- [ ] **X-Content-Type-Options**: `nosniff` ✅
- [ ] **Strict-Transport-Security**: `max-age=31536000` (recomendado)

---

## 🔍 PASO 3: Validar en HestiaCP

1. **Acceso SSH al VPS**:
   ```bash
   ssh root@tu-vps.com
   cd /root/apps/miapi-local
   ```

2. **Verificar logs en tiempo real**:
   ```bash
   docker-compose -f docker-compose.prod.yml logs -f php
   docker-compose -f docker-compose.prod.yml logs -f nginx
   ```

3. **Verificar tabla de sesiones**:
   ```bash
   docker-compose -f docker-compose.prod.yml exec mysql mysql -u${DB_USERNAME} -p${DB_PASSWORD} ${DB_DATABASE} -e "SHOW TABLES LIKE 'sessions';"
   ```

---

## 🌐 PASO 4: Pruebas Finales

### Test 1: Logo y Assets Básicos
```bash
curl -i https://api.nortedesantander.com/
# Debe retornar 200 OK y contenido HTML
```

### Test 2: Admin Filament (Crítico)
```bash
# Acceder a https://api.nortedesantander.com/admin
# Debería cargar:
# ✓ CSS/Tailwind (estilos completos)
# ✓ JS Filament (interactividad)
# ✓ Login form con estilos
```

### Test 3: Headers de Sesión
```bash
curl -i -H "Host: api.nortedesantander.com" http://127.0.0.1:8085/admin 2>/dev/null | grep -i "set-cookie\|domain\|secure"
```

**Debe mostrar**:
```
Set-Cookie: LARAVEL_SESSION=...; Path=/; Domain=.nortedesantander.com; Secure; HttpOnly; SameSite=Lax
```

### Test 4: Cloudflare Ray ID
```bash
curl -i https://api.nortedesantander.com/ | grep -i "cf-ray\|cf-cache-status"
```

---

## 🐛 Troubleshooting: Si aún no ves estilos

### Causa 1: Vite Manifest no existe
```bash
docker-compose -f docker-compose.prod.yml exec php php artisan filament:install --force
docker-compose -f docker-compose.prod.yml exec php npm run build
```

### Causa 2: Cloudflare está minificando Assets
→ En Cloudflare Dashboard → Speed → Minification:
- [x] Desactivar "Auto Minify" temporalmente para Filament assets

### Causa 3: Cache Headers incorrectos
```bash
docker-compose -f docker-compose.prod.yml exec nginx nginx -t
docker-compose -f docker-compose.prod.yml exec nginx nginx -s reload
```

### Causa 4: SESSION_DRIVER aún no migrado
```bash
docker-compose -f docker-compose.prod.yml exec php php artisan cache:clear
docker-compose -f docker-compose.prod.yml exec php php artisan config:cache
```

---

## 📋 Resumen de Variables Críticas

| Variable | Valor | Por qué |
|----------|-------|--------|
| `APP_URL` | `https://api.nortedesantander.com` | Base de URLs dinámicas |
| `SANCTUM_STATEFUL_DOMAINS` | `api.nortedesantander.com,api.nortedesantander.com:443,nortedesantander.com` | Cookies de sesión permiten auth |
| `SESSION_DOMAIN` | `.nortedesantander.com` | Cookies disponibles en subdomain |
| `SESSION_DRIVER` | `database` | Sesiones persistentes en BD |
| `TRUSTED_PROXIES` | `*` | Confía en Cloudflare/Nginx headers |
| `LOG_LEVEL` | `warning` | No saturar logs en producción |

---

## 📞 Siguientes Pasos

Si después de esto aún ves problemas:
1. Compartir output de: `docker-compose -f docker-compose.prod.yml logs php`
2. Revisar console DevTools en navegador (F12)
3. Verificar headers con: `curl -v https://api.nortedesantander.com/admin`
