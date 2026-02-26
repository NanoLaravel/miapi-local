# GitFlow Recomendado para el Proyecto

## 🌿 Estructura de Ramas

```
main (producción)
  ↑
develop (integración)
  ↑
feature/* (características)
  ↑
hotfix/* (correcciones urgentes)
```

---

## 🔄 Flujo de Trabajo

### 1. Desarrollo de Nuevas Funcionalidades

```bash
# 1. Siempre desde develop
git checkout develop
git pull origin develop

# 2. Crear rama de característica
git checkout -b feature/nombre-caracteristica

# 3. Trabajar y hacer commits frecuentes
git add .
git commit -m "feat: descripción clara"

# 4. Subir rama al remoto
git push origin feature/nombre-caracteristica

# 5. Crear Pull Request hacia develop
# (En GitHub: comparar develop <- feature/nombre-caracteristica)
```

### 2. Antes de Producción

```bash
# 1. Desde develop, crear rama release
git checkout -b release/v1.0.0

# 2. En esta rama:
# - Ejecutar pruebas
# - Revisar seguridad
# - Actualizar versión
# - Hacer ajustes finales

# 3. Merge a main Y develop
git checkout main
git merge release/v1.0.0
git push origin main

git checkout develop
git merge release/v1.0.0
git push origin develop

# 4. Eliminar rama release
git branch -d release/v1.0.0
git push origin --delete release/v1.0.0
```

### 3. Corrección Urgente en Producción

```bash
# Desde main
git checkout -b hotfix/descripcion-problema

# Corregir, testear, commit
git commit -m "fix: descripción"

# Merge a main y develop
git checkout main
git merge hotfix/descripcion-problema
git push origin main

git checkout develop
git merge hotfix/descripcion-problema
git push origin develop

# Eliminar rama
git branch -d hotfix/descripcion-problema
```

---

## ⚠️ Pendientes Antes de Producción

### 🔒 Seguridad
- [ ] Revisar vulnerabilidades con `composer audit`
- [ ] Verificar que no haya credenciales hardcodeadas
- [ ] Configurar HTTPS/SSL
- [ ] Revisar headers de seguridad
- [ ] Validar entrada de datos (Laravel validation)

### 🧪 Pruebas
- [ ] PHPUnit: pruebas unitarias
- [ ] PHPUnit: pruebas de integración
- [ ] Probar endpoints API con Postman/Insomnia
- [ ] Probar flujos de autenticación
- [ ] Probar Filament admin

### 📝 Documentación
- [ ] Actualizar README.md
- [ ] Documentar variables de entorno
- [ ] Documentar despliegue a VPS
- [ ] Scribe: actualizar documentación API (`php artisan scribe:generate`)

### 🔧 Configuración Production
- [ ] APP_ENV=production
- [ ] APP_DEBUG=false
- [ ] APP_KEY generada
- [ ] DB configurada
- [ ] Logs configurados
- [ ] Sanctum domains configurados

---

## 🐳 Despliegue a VPS con Docker

### En el VPS (Ubuntu):

```bash
# 1. Instalar Docker
sudo apt update
sudo apt install -y docker.io docker-compose

# 2. Verificar instalación
docker --version
docker-compose --version

# 3. Si no tienes Docker Compose plugin:
sudo curl -L "https://github.com/docker/compose/releases/download/v2.24.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose
```

### Opcional: Docker + Portainer (UI para Docker):
```bash
# Instalar Portainer (interfaz gráfica)
docker volume create portainer_data
docker run -d -p 9000:9000 --name=portainer --restart=always -v /var/run/docker.sock:/var/run/docker.sock -v portainer_data:/data portainer/portainer-ce:latest
```

### Despliegue:

```bash
# 1. Clonar repositorio
git clone https://github.com/NanoLaravel/miapi-local.git
cd miapi-local

# 2. Copiar y configurar .env
cp src/.env.example src/.env
# Editar src/.env con valores de producción

# 3. Construir y ejecutar
docker-compose up -d --build

# 4. Ejecutar migraciones
docker-compose exec app php artisan migrate --force

# 5. Generar clave si es necesario
docker-compose exec app php artisan key:generate
```

---

## 📌 Reglas de Commits

Usar [Conventional Commits](https://www.conventionalcommits.org/):

```
feat: nueva funcionalidad
fix: corrección de bug
docs: documentación
style: formato (sin cambio de código)
refactor:重构
test: agregar tests
chore: mantenimiento
```

Ejemplos:
```
feat: Add user profile endpoint
fix: Resolve login redirect issue
docs: Update API documentation
security: Add CSRF protection headers
```

---

## 🏷️ Versionado Semántico

Formato: `MAJOR.MINOR.PATCH`

- **MAJOR**: Cambios incompatibles
- **MINOR**: Nueva funcionalidad compatible
- **PATCH**: Correcciones compatibles

Ejemplo: `v1.2.0` → `v1.2.1` → `v2.0.0`
