<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  </a>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11.x-red?style=flat&logo=laravel" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.3-blueviolet?style=flat&logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-8.0-blue?style=flat&logo=mysql" alt="MySQL">
  <img src="https://img.shields.io/badge/Docker-24.0-blue?style=flat&logo=docker" alt="Docker">
</p>

# 🎯 API REST - Places, Events & Advertisements

API RESTful completa desarrollada con Laravel 11 y Sanctum para autenticación. Incluye panel de administración con FilamentPHP.

## 🛠️ Tecnologías

| Categoría | Tecnología |
|-----------|------------|
| **Backend** | Laravel 11.x |
| **PHP** | 8.3+ |
| **Base de Datos** | MySQL 8.0 |
| **Autenticación** | Laravel Sanctum + Socialite |
| **Frontend Admin** | FilamentPHP 3.x |
| **Documentación** | Scribe (OpenAPI) |
| **Contenedores** | Docker + Docker Compose |
| **Permisos** | Spatie Permissions |

## ✨ Características

### API REST
- ✅ Autenticación con tokens (Sanctum)
- ✅ Login social (Google, Facebook)
- ✅ CRUD completo de Places, Categories, Images, Reviews
- ✅ Sistema de favoritos
- ✅ Filtros avanzados de búsqueda
- ✅ Control de acceso por roles (User, Editor, Admin)

### Panel de Administración (Filament)
- ✅ Gestión visual de lugares
- ✅ Gestión de eventos y publicidad
- ✅ Sistema de roles y permisos
- ✅ Widgets de estadísticas
- ✅ Interfaz en español

### Eventos y Publicidad
- ✅ Eventos (crear, editar, eliminar)
- ✅ Publicidad con posiciones
- ✅ Tracking de clicks
- ✅ Imágenes polimórficas

## 📡 Endpoints Principales

```
POST   /api/login              # Autenticación
POST   /api/register           # Registro público
POST   /api/logout             # Cerrar sesión

GET    /api/places             # Listar lugares
GET    /api/events             # Listar eventos
GET    /api/advertisements     # Listar publicidad

# Rutas protegidas requieren token:
Authorization: Bearer <token>
```

📖 **Documentación completa**: `/docs` (cuando el servidor está corriendo)

## 🚀 Instalación

### Requisitos
- Docker y Docker Compose
- Git

### Pasos

```bash
# 1. Clonar repositorio
git clone https://github.com/NanoLaravel/miapi-local.git
cd miapi-local

# 2. Configurar entorno
cp src/.env.example src/.env

# 3. Iniciar contenedores
docker-compose up -d --build

# 4. Instalar dependencias
docker-compose exec app composer install

# 5. Generar clave
docker-compose exec app php artisan key:generate

# 6. Ejecutar migraciones
docker-compose exec app php artisan migrate

# 7. Poblar base de datos (opcional)
docker-compose exec app php artisan db:seed

# 8. Generar documentación API
docker-compose exec app php artisan scribe:generate
```

### URLs de Acceso
| Servicio | URL |
|----------|-----|
| API | `http://localhost/api` |
| Documentación | `http://localhost/docs` |
| Admin Panel | `http://localhost/admin` |
| phpMyAdmin | `http://localhost:8090` |

## 🔐 Roles y Permisos

| Rol | Permisos |
|-----|----------|
| **User** | Ver lugares, eventos, publicidad. Gestionar favoritos. |
| **Editor** | Todo lo anterior + CRUD de recursos. |
| **Admin** | Todo lo anterior + eliminación + gestión de usuarios. |

## 🐳 Docker

El proyecto incluye configuración completa de Docker:

- **PHP-FPM 8.3** - Aplicación Laravel
- **Nginx** - Servidor web
- **MySQL 8.0** - Base de datos
- **phpMyAdmin** - Gestión de DB

### Comandos útiles

```bash
# Ver logs
docker-compose logs -f app

# Acceder al contenedor
docker-compose exec app bash

# Regenerar clave
docker exec laravel_php php /var/www/html/artisan key:generate
```

## 📁 Estructura del Proyecto

```
miapi-local/
├── docker/              # Scripts de Docker
├── nginx/               # Configuración Nginx
├── src/
│   ├── app/
│   │   ├── Filament/   # Recursos del panel admin
│   │   ├── Http/       # Controllers y Middlewares
│   │   └── Models/     # Modelos Eloquent
│   ├── database/
│   │   ├── migrations/ # Migraciones
│   │   └── seeders/   # Seeders
│   └── routes/
│       └── api.php    # Rutas API
└── docker-compose.yml
```

## ⚠️ Seguridad

- ❌ **NO** exponer credenciales reales
- ✅ Usar variables de entorno (`.env`)
- ✅ Regenerar `APP_KEY` en producción
- ✅ Configurar `APP_DEBUG=false` en producción
- ✅ Usar HTTPS en producción
- ✅ Tokens de Sanctum con expiración configurada

## 📝 Configuración para Producción

Ver archivo [`.env.example`](.env.example) para las variables requeridas.

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com
APP_KEY=<generar con artisan>

DB_HOST=mysql
DB_DATABASE=tu_db
DB_USERNAME=tu_user
DB_PASSWORD=tu_password

SANCTUM_STATEFUL_DOMAINS=tu-dominio.com
```

## 🤝 Contribuir

1. Fork del repositorio
2. Crear rama (`git checkout -b feature/foo`)
3. Commit cambios (`git commit -m 'feat: agregar algo'`)
4. Push a la rama (`git push origin feature/foo`)
5. Crear Pull Request

## 📄 Licencia

MIT License. Ver [LICENSE](LICENSE) para más detalles.

---

<p align="center">Desarrollado con ❤️ usando Laravel</p>
