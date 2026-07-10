Plan de Implementación: Nueva Sección de "Productos Locales" (Actualizado)
Este plan detalla los pasos para diseñar, implementar y desplegar la nueva sección "Productos Locales" (Local Products) sin la necesidad de dividir en categorías, ya que el catálogo total proyectado es pequeño (máximo ~50 productos) y se mantendrá de forma plana e independiente.

La funcionalidad constará de la parte administrativa (Filament PHP) para gestionar los productos y de endpoints en la API REST (Laravel Sanctum) para que la aplicación móvil los consuma.

Decisiones de Diseño
Catálogo Plano (Sin Categorías):
Se elimina por completo el modelo y la tabla de ProductCategory. Los productos se listarán en una única sección plana.
Modelo de Producto (LocalProduct):
Tendrá campos específicos para datos de contacto (teléfono, redes sociales).
Guardará relación con el usuario creador (user_id).
El precio será opcional/nullable.
Galería Polimórfica de Imágenes:
Reutilizaremos la tabla polimórfica images mediante una relación morphMany. Así evitamos crear una nueva tabla y mantenemos la coherencia del sistema de archivos.
Enlace Directo a WhatsApp:
Para agilizar el trabajo en la app móvil, el modelo LocalProduct incluirá un atributo dinámico (accessor) llamado whatsapp_url. Este procesará automáticamente el teléfono del productor y generará el enlace directo https://wa.me/ con un mensaje personalizado.
Si el número ingresado no tiene indicativo de país (por ejemplo, tiene 10 dígitos estándar de Colombia), le inyectaremos automáticamente el prefijo 57 para que el enlace de WhatsApp funcione correctamente.
Políticas de Acceso:
Implementaremos la política LocalProductPolicy consistente con las del resto del sistema usando el trait HasRoleAuthorization.
Cambios Propuestos
1. Base de Datos (Migraciones)
[NEW] 
2026_07_09_000001_create_local_products_table.php
Crear tabla para los productos locales:

php

Schema::create('local_products', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->decimal('price', 10, 2)->nullable();
    $table->text('description');
    $table->string('producer_name');
    $table->string('approximate_location')->nullable();
    $table->string('phone');
    $table->string('facebook_url')->nullable();
    $table->string('instagram_url')->nullable();
    $table->boolean('is_active')->default(true);
    $table->boolean('is_featured')->default(false);
    $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
    $table->timestamps();
});
2. Modelo de Eloquent
[NEW] 
LocalProduct.php
php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;
class LocalProduct extends Model
{
    protected $fillable = [
        'name',
        'price',
        'description',
        'producer_name',
        'approximate_location',
        'phone',
        'facebook_url',
        'instagram_url',
        'is_active',
        'is_featured',
        'user_id'
    ];
    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];
    protected $appends = ['whatsapp_url', 'first_image_url'];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }
    public function getFirstImageUrlAttribute(): ?string
    {
        $firstImage = $this->images()->first();
        return $firstImage ? $firstImage->path : null;
    }
    public function getWhatsappUrlAttribute(): string
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $this->phone);
        if (strlen($cleanPhone) === 10 && !str_starts_with($cleanPhone, '57')) {
            $cleanPhone = '57' . $cleanPhone;
        }
        $message = "Hola, vi tu producto '{$this->name}' en la app y me gustaría obtener más información.";
        return "https://wa.me/{$cleanPhone}?text=" . urlencode($message);
    }
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }
}
3. Política de Acceso (Policy)
[NEW] 
LocalProductPolicy.php
php

namespace App\Policies;
use App\Models\User;
use App\Models\LocalProduct;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Concerns\HasRoleAuthorization;
class LocalProductPolicy
{
    use HandlesAuthorization, HasRoleAuthorization;
    public function viewAny(User $user): bool { return $this->canViewContent($user); }
    public function view(User $user, LocalProduct $product): bool { return $this->canViewContent($user); }
    public function create(User $user): bool { return $this->canManageContent($user); }
    public function update(User $user, LocalProduct $product): bool { return $this->canManageContent($user); }
    public function delete(User $user, LocalProduct $product): bool { return $this->canDeleteContent($user); }
}
4. Controlador API y Rutas
[NEW] 
LocalProductController.php
Controlador de recursos API para productos. Incluirá:

index() con filtros por active, featured, search y paginación.
featured() para obtener solo productos destacados.
[MODIFY] 
api.php
Registrar las rutas bajo el middleware de Sanctum:

php

// Productos Locales
    Route::get('local-products/featured', [LocalProductController::class, 'featured']);
    Route::apiResource('local-products', LocalProductController::class);
5. Recursos de Filament (Dashboard)
[NEW] 
LocalProductResource.php
Formulario completo y optimizado:

Campos agrupados por Secciones (Detalles del Producto, Información del Productor).
El campo initial_images se mostrará solo en la creación (create) como opcional (minItems(0) y sin required() internos).
El campo images de relación polimórfica se mostrará solo en la edición (edit).
Adición de páginas: ListLocalProducts, CreateLocalProduct (con hook afterCreate() para asociar imágenes) y EditLocalProduct.
Plan de Verificación
Pruebas Automatizadas
Crearemos una suite de pruebas feature en:

tests/Feature/LocalProductApiTest.php
Validar que un usuario autenticado pueda listar y ver productos destacados.
Verificar que se calcule y retorne correctamente la url de WhatsApp y la primera imagen de la galería.
Validar que un usuario no autenticado sea rechazado.
Pruebas Manuales
Ejecutar las migraciones localmente (docker exec laravel_php php artisan migrate).
Entrar al Dashboard de Filament e ingresar un par de productos locales de prueba.
Verificar desde Postman o CURL que los endpoints /api/local-products retornen la información y los accesores con éxito.