# Contexto del Proyecto - API Lugares Turísticos

## Descripción General

Esta es una API RESTful desarrollada con **Laravel 12** y **FilamentPHP** que proporciona información sobre lugares turísticos como hoteles, restaurantes y actividades recreativas. Además, incluye gestión de **eventos locales** y **publicidad** para la aplicación móvil Android.

---

## Tecnologías Utilizadas

| Tecnología | Versión | Propósito |
|------------|---------|-----------|
| Laravel | 12.x | Framework PHP backend |
| FilamentPHP | 3.x | Panel de administración |
| Laravel Sanctum | - | Autenticación API tokens |
| Spatie Permission | - | Gestión de roles y permisos |
| Laravel Socialite | - | Autenticación social (Google, Facebook) |
| MySQL | 8.x | Base de datos |
| Docker | - | Contenedorización |
| Nginx | - | Servidor web |

---

## Estructura de Directorios

```
miapi-local/
├── docker/                    # Scripts de Docker
│   └── scripts/
│       ├── migrate.sh         # Ejecutar migraciones
│       ├── seed.sh            # Ejecutar seeders
│       └── start.sh           # Iniciar aplicación
├── dockerfiles/               # Dockerfiles
│   ├── composer.dockerfile
│   ├── nginx.dockerfile
│   └── php.dockerfile
├── nginx/                     # Configuración Nginx
│   └── default.conf
├── src/                       # Código fuente Laravel
│   ├── app/
│   │   ├── Exceptions/        # Manejo de excepciones
│   │   ├── Filament/          # Recursos FilamentPHP
│   │   │   ├── Resources/     # CRUD admin resources
│   │   │   └── Widgets/       # Widgets del dashboard
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   └── Api/       # Controladores API REST
│   │   │   └── Middleware/    # Middleware personalizado
│   │   ├── Models/            # Modelos Eloquent
│   │   ├── Policies/          # Policies para autorización
│   │   └── Providers/         # Service providers
│   ├── bootstrap/             # Bootstrap de Laravel
│   ├── config/                # Archivos de configuración
│   ├── database/
│   │   ├── factories/         # Model factories
│   │   ├── migrations/        # Migraciones de BD
│   │   └── seeders/           # Seeders
│   ├── public/                # Archivos públicos
│   └── routes/
│       └── api.php            # Rutas API
├── docker-compose.yml         # Orquestación Docker
├── Makefile                   # Comandos make
└── README.md
```

---

## Modelo de Datos

### Diagrama Entidad-Relación

```
┌─────────────────┐
│      User       │
├─────────────────┤
│ id (PK)         │
│ name            │
│ email (unique)  │
│ password        │
│ remember_token  │
│ created_at      │
│ updated_at      │
└────────┬────────┘
         │
         │ N:M (favorites)
         │
         ▼
┌─────────────────┐       ┌─────────────────┐       ┌─────────────────┐
│      Place      │       │    Category     │       │  place_category │
├─────────────────┤       ├─────────────────┤       ├─────────────────┤
│ id (PK)         │       │ id (PK)         │       │ id (PK)         │
│ name            │       │ name            │       │ place_id (FK)   │
│ description     │       │ description     │       │ category_id(FK) │
│ address         │       │ created_at      │       │ created_at      │
│ latitude        │       │ updated_at      │       │ updated_at      │
│ longitude       │       └─────────────────┘       └─────────────────┘
│ phone           │              ▲
│ website         │              │ N:M
│ type (enum)     │──────────────┘
│ rating          │
│ facilities(JSON)│
│ created_at      │
│ updated_at      │
└────────┬────────┘
         │
         │ 1:N
    ┌────┴────┬─────────────┐
    │         │             │
    ▼         ▼             ▼
┌───────┐ ┌───────┐   ┌─────────┐
│ Image │ │ Review│   │  Price  │
├───────┤ ├───────┤   ├─────────┤
│id(PK) │ │id(PK) │   │ id (PK) │
│place_id│ │place_id│  │place_id │
│path   │ │user_id│   │ type    │
│desc   │ │rating │   │ value   │
└───────┘ │comment│   │ currency│
          │items* │   │ desc    │
          └───────┘   └─────────┘
```

*Review items: cleanliness, accuracy, check_in, communication, location, price

### Descripción de Tablas

#### `users`
Usuarios del sistema con autenticación Sanctum y roles Spatie.

#### `places`
Lugares turísticos principales.
- `type`: enum ('restaurant', 'hotel', 'recreation', 'other')
- `facilities`: JSON con lista de instalaciones

#### `categories`
Categorías para clasificar lugares (ej: "Playa", "Montaña", "Urbano").

#### `place_category` (pivot)
Relación N:M entre places y categories.

#### `images`
Imágenes asociadas a un lugar.
- `path`: ruta relativa en storage/app/public

#### `reviews`
Reseñas de usuarios sobre lugares.
- Incluye rating general y items específicos (cleanliness, accuracy, etc.)

#### `prices`
Precios asociados a un lugar (ej: "Adulto", "Niño", "Entrada").

#### `favorites`
Relación N:M entre users y places para favoritos.

#### `events`
Eventos locales (conciertos, ferias, festividades, etc.).
- `start_date`, `end_date`: Fechas de inicio y fin
- `is_active`: Si el evento está activo
- `is_featured`: Si el evento está destacado
- `place_id`: Lugar asociado (opcional)
- `price`: Precio de entrada (opcional)

#### `advertisements`
Publicidad local (banners, popups, etc.).
- `type`: enum ('banner', 'popup', 'sidebar', 'inline')
- `position`: enum ('home', 'places', 'events', 'all')
- `start_date`, `end_date`: Período de vigencia
- `priority`: Orden de visualización (mayor = primero)
- `clicks_count`, `views_count`: Estadísticas de rendimiento

---

## Modelos Eloquent

### Place (`src/app/Models/Place.php`)

```php
// Relaciones
$place->categories()    // BelongsToMany (Category)
$place->images()        // HasMany (Image)
$place->reviews()       // HasMany (Review)
$place->favorites()     // BelongsToMany (User)
$place->prices()        // HasMany (Price)

// Casts
'facilities' => 'array'  // JSON a array PHP
```

### User (`src/app/Models/User.php`)

```php
// Traits
HasApiTokens, HasFactory, Notifiable, HasRoles

// Relaciones
$user->favoritePlaces()  // BelongsToMany (Place)
```

### Review (`src/app/Models/Review.php`)

```php
// Accessor
$review->items_average  // Promedio de items calificados

// Relaciones
$review->place()  // BelongsTo (Place)
$review->user()   // BelongsTo (User)
```

### Event (`src/app/Models/Event.php`)

```php
// Relaciones
$event->place()  // BelongsTo (Place) - opcional
$event->user()   // BelongsTo (User) - creador

// Scopes
Event::active()     // Eventos activos
Event::featured()   // Eventos destacados
Event::current()    // Eventos actuales (no finalizados)
Event::upcoming()   // Eventos próximos
Event::ongoing()    // Eventos en curso

// Métodos
$event->isOngoing()    // ¿Está en curso?
$event->hasEnded()     // ¿Ya terminó?
$event->isUpcoming()   // ¿Es próximo?
```

### Advertisement (`src/app/Models/Advertisement.php`)

```php
// Constantes
Advertisement::TYPE_BANNER, TYPE_POPUP, TYPE_SIDEBAR, TYPE_INLINE
Advertisement::POSITION_HOME, POSITION_PLACES, POSITION_EVENTS, POSITION_ALL

// Relaciones
$ad->place()  // BelongsTo (Place) - opcional
$ad->user()   // BelongsTo (User) - creador

// Scopes
Advertisement::active()              // Anuncios activos y vigentes
Advertisement::byPosition($pos)      // Filtrar por posición
Advertisement::byType($type)         // Filtrar por tipo
Advertisement::orderedByPriority()   // Ordenar por prioridad

// Métodos
$ad->incrementViews()   // Incrementar contador de vistas
$ad->incrementClicks()  // Incrementar contador de clics
$ad->isValid()          // ¿Está vigente?
```

---

## API REST - Endpoints

### Rutas Públicas (sin autenticación)

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/login` | Iniciar sesión |
| POST | `/api/register` | Registro público (rol: user) |
| GET | `/api/places` | Listar lugares (paginado) |
| GET | `/api/auth/google/redirect` | Redirección a Google OAuth |
| GET | `/api/auth/google/callback` | Callback de Google |
| GET | `/api/auth/facebook/redirect` | Redirección a Facebook OAuth |
| GET | `/api/auth/facebook/callback` | Callback de Facebook |

#### Eventos (público)

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/events` | Listar eventos (con filtros) |
| GET | `/api/events/featured` | Eventos destacados |
| GET | `/api/events/upcoming` | Eventos próximos |
| GET | `/api/events/ongoing` | Eventos en curso |
| GET | `/api/events/place/{placeId}` | Eventos de un lugar |
| GET | `/api/events/{id}` | Detalle de evento |

#### Publicidad (público)

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/advertisements` | Listar anuncios |
| GET | `/api/advertisements/position/{position}` | Anuncios por posición |
| GET | `/api/advertisements/banners` | Banners activos |
| GET | `/api/advertisements/{id}` | Detalle de anuncio |
| POST | `/api/advertisements/{id}/click` | Registrar clic |

### Rutas Protegidas (auth:sanctum)

#### Rol: user, editor, admin

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/logout` | Cerrar sesión |
| GET | `/api/places/filter/type/{type}` | Filtrar por tipo |
| GET | `/api/places/filter/category/{id}` | Filtrar por categoría |
| GET | `/api/places/search/{name}` | Buscar por nombre |
| GET | `/api/places/rating/{min}/{max}` | Filtrar por rango de rating |
| GET | `/api/places/best-reviews` | Lugares con mejores reseñas |
| GET | `/api/places/type/{type}` | Lugares por tipo |
| GET | `/api/places/category/{id}` | Lugares por categoría |
| GET | `/api/places/{id}/reviews` | Reseñas de un lugar |
| GET | `/api/categories` | Listar categorías |
| GET | `/api/images` | Listar imágenes |
| GET | `/api/reviews` | Listar reseñas |
| GET | `/api/favorites` | Favoritos del usuario |
| POST | `/api/places/{id}/favorite` | Agregar a favoritos |
| DELETE | `/api/places/{id}/favorite` | Quitar de favoritos |

#### Rol: editor, admin

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/places` | Crear lugar |
| PUT/PATCH | `/api/places/{id}` | Actualizar lugar |
| POST | `/api/categories` | Crear categoría |
| PUT/PATCH | `/api/categories/{id}` | Actualizar categoría |
| POST | `/api/images` | Crear imagen |
| PUT/PATCH | `/api/images/{id}` | Actualizar imagen |
| POST | `/api/reviews` | Crear reseña |
| PUT/PATCH | `/api/reviews/{id}` | Actualizar reseña |
| POST | `/api/events` | Crear evento |
| PUT/PATCH | `/api/events/{id}` | Actualizar evento |
| POST | `/api/advertisements` | Crear anuncio |
| PUT/PATCH | `/api/advertisements/{id}` | Actualizar anuncio |

#### Rol: admin (exclusivo)

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| DELETE | `/api/places/{id}` | Eliminar lugar |
| DELETE | `/api/categories/{id}` | Eliminar categoría |
| DELETE | `/api/images/{id}` | Eliminar imagen |
| DELETE | `/api/reviews/{id}` | Eliminar reseña |
| DELETE | `/api/events/{id}` | Eliminar evento |
| DELETE | `/api/advertisements/{id}` | Eliminar anuncio |
| POST | `/api/admin/register` | Registrar usuario con rol específico |

---

## Panel de Administración FilamentPHP

### Acceso
- URL: `/admin`
- Requiere autenticación de usuario con acceso al panel

### Recursos Disponibles

| Recurso | Archivo | Descripción |
|---------|---------|-------------|
| Places | `PlaceResource.php` | Gestión de lugares con imágenes, reseñas y precios |
| Categories | `CategoryResource.php` | Gestión de categorías |
| Images | `ImageResource.php` | Gestión de imágenes |
| Reviews | `ReviewResource.php` | Gestión de reseñas |
| Prices | `PriceResource.php` | Gestión de precios |
| Events | `EventResource.php` | Gestión de eventos locales |
| Advertisements | `AdvertisementResource.php` | Gestión de publicidad |
| Users | `UserResource.php` | Gestión de usuarios |
| Users | `UserResource.php` | Gestión de usuarios |

### Relation Managers (PlaceResource)

- `ImagesRelationManager` - Gestión de imágenes
- `ReviewsRelationManager` - Gestión de reseñas
- `PricesRelationManager` - Gestión de precios

### Widgets

- `StatsOverviewWidget` - Estadísticas generales
- `GrowthChartWidget` - Gráfico de crecimiento

---

## Sistema de Roles y Permisos

### Roles Definidos

| Rol | Descripción | Permisos API |
|-----|-------------|--------------|
| `user` | Usuario básico | Leer, favoritos |
| `editor` | Editor de contenido | CRUD de lugares, categorías, imágenes, reseñas |
| `admin` | Administrador | Todos los permisos + eliminar + gestión de usuarios |

### Middleware de Roles

```php
// En routes/api.php
Route::middleware(['role:user|editor|admin'])->group(...)
Route::middleware(['role:editor|admin'])->group(...)
Route::middleware(['role:admin'])->group(...)
```

### Policies

Cada modelo tiene su Policy correspondiente:
- `CategoryPolicy`
- `ImagePolicy`
- `PlacePolicy`
- `PricePolicy`
- `ReviewPolicy`
- `UserPolicy`
- `RolePolicy`
- `EventPolicy`
- `AdvertisementPolicy`

---

## Comandos Útiles

### Docker

```bash
# Construir contenedores
docker-compose up -d --build

# Ver logs
docker-compose logs -f app

# Ejecutar comandos en contenedor
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
```

### Makefile

```bash
make up        # Iniciar contenedores
make down      # Detener contenedores
make migrate   # Ejecutar migraciones
make seed      # Ejecutar seeders
make shell     # Acceder al contenedor
```

### Artisan

```bash
# Migraciones
php artisan migrate
php artisan migrate:fresh --seed

# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Generar recursos Filament
php artisan make:filament-resource Place
php artisan make:filament-relation-manager PlaceResource images title

# Generar documentación API (Scribe)
php artisan scribe:generate
```

---

## Validaciones de API

### Place

```php
'name' => 'required|string|max:255',
'description' => 'nullable|string',
'address' => 'required|string|max:255',
'latitude' => 'required|numeric',
'longitude' => 'required|numeric',
'phone' => 'nullable|string|max:50',
'website' => 'nullable|url|max:255',
'type' => 'required|in:restaurant,hotel,recreation,other',
'category_ids' => 'array',
'category_ids.*' => 'exists:categories,id',
'facilities' => 'nullable', // string o array
```

### Review

```php
'place_id' => 'required|exists:places,id',
'user_id' => 'required|exists:users,id',
'rating' => 'required|integer|min:1|max:5',
'comment' => 'nullable|string',
// Items de reseña (opcionales)
'cleanliness', 'accuracy', 'check_in', 'communication', 'location', 'price'
```

### Event

```php
'title' => 'required|string|max:255',
'description' => 'nullable|string',
'start_date' => 'required|date|after:now',
'end_date' => 'required|date|after:start_date',
'location' => 'required|string|max:255',
'latitude' => 'nullable|numeric|between:-90,90',
'longitude' => 'nullable|numeric|between:-180,180',
'image_path' => 'nullable|image|max:5120', // 5MB max
'is_active' => 'boolean',
'is_featured' => 'boolean',
'price' => 'nullable|numeric|min:0',
'contact_phone' => 'nullable|string|max:50',
'contact_email' => 'nullable|email|max:255',
'website' => 'nullable|url|max:255',
'place_id' => 'nullable|exists:places,id',
```

### Advertisement

```php
'title' => 'required|string|max:255',
'description' => 'nullable|string',
'image_path' => 'required|image|max:5120', // 5MB max
'link_url' => 'nullable|url|max:255',
'type' => 'required|in:banner,popup,sidebar,inline',
'position' => 'required|in:home,places,events,all',
'start_date' => 'required|date',
'end_date' => 'required|date|after:start_date',
'is_active' => 'boolean',
'priority' => 'integer|min:0|max:100',
'place_id' => 'nullable|exists:places,id',
```

---

## Configuración

### Variables de Entorno (.env)

```env
APP_NAME=MiAPI
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=miapi
DB_USERNAME=miapi
DB_PASSWORD=secret

SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1

# Socialite
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
FACEBOOK_CLIENT_ID=
FACEBOOK_CLIENT_SECRET=
```

---

## Notas de Desarrollo

1. **Autenticación**: Usar Laravel Sanctum para API tokens. Incluir header `Authorization: Bearer {token}` en requests protegidos.

2. **Subida de imágenes**: Las imágenes se almacenan en `storage/app/public/images/`. Usar `php artisan storage:link` para crear el symlink.

3. **Facilities**: Campo JSON en places. Puede enviarse como string separado por comas o como array.

4. **Paginación**: Los endpoints de listado usan paginación con 10 elementos por página.

5. **Rate Limiting**: Configurar en `AppServiceProvider` si se necesita limitar requests.

---

## Archivos de Referencia Rápida

| Propósito | Archivo |
|-----------|---------|
| Rutas API | `src/routes/api.php` |
| Modelos | `src/app/Models/*.php` |
| Controladores API | `src/app/Http/Controllers/Api/*.php` |
| Recursos Filament | `src/app/Filament/Resources/*.php` |
| Migraciones | `src/database/migrations/*.php` |
| Seeders | `src/database/seeders/*.php` |
| Config Auth | `src/config/auth.php` |
| Config Sanctum | `src/config/sanctum.php` |
| Config Permisos | `src/config/permission.php` |