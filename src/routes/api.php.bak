<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    PlaceController,
    CategoryController,
    ImageController,
    ReviewController,
    AuthController,
    PlaceFilterController,
    EventController,
    AdvertisementController
};


/**
 * ╔══════════════════════════════════╗
════════════════════════════════ * ║                    🔓 RUTAS PÚBLICAS                               ║
 * ║  Estas son las únicas rutas accesibles ║
 * sin autenticación          ║  Necessarias para login/registro de nuevos usuarios              ║
 * ╚══════════════════════════════════════════════════════════════════╝
 */
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'registerPublic']);

// Login Social (OAuth - necesita ser público para el callback)
Route::get('auth/google/redirect', [AuthController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
Route::get('auth/facebook/redirect', [AuthController::class, 'redirectToFacebook']);
Route::get('auth/facebook/callback', [AuthController::class, 'handleFacebookCallback']);


/**
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║                    🔐 RUTAS PROTEGIDAS                             ║
 * ║  Todas las demás rutas requieren autenticación con Sanctum        ║
 * ║  Uso: Header "Authorization: Bearer <token>"                     ║
 * ╚══════════════════════════════════════════════════════════════════╝
 */
Route::middleware(['auth:sanctum'])->group(function () {

    // ═══════════════════════════════════════════════════════════════
    // 🔸 AUTENTICACIÓN
    // ═══════════════════════════════════════════════════════════════
    Route::post('logout', [AuthController::class, 'logout']);


    // ═══════════════════════════════════════════════════════════════
    // 👤 RUTAS PARA USER, EDITOR Y ADMIN
    // ═══════════════════════════════════════════════════════════════
    Route::middleware(['role:user|editor|admin'])->group(function () {

        // ─────────────────────────────────────────────────────────────
        // 📍 LUGARES - Lectura completa (ahora protegido)
        // ─────────────────────────────────────────────────────────────
        Route::apiResource('places', PlaceController::class)->only(['index', 'show']);
        
        // Filtros avanzados
        Route::get('places/filter/type/{type}', [PlaceFilterController::class, 'filterByType']);
        Route::get('places/filter/category/{categoryId}', [PlaceFilterController::class, 'filterByCategory']);
        Route::get('places/search/{name}', [PlaceFilterController::class, 'searchByName']);
        Route::get('places/rating/{min}/{max}', [PlaceFilterController::class, 'ratingRange']);
        Route::get('places/best-reviews', [PlaceFilterController::class, 'bestReviews']);
        Route::get('places/any-facility', [PlaceFilterController::class, 'anyFacility']);

        // Explorar lugares con filtros básicos
        Route::get('places/type/{type}', [PlaceController::class, 'byType']);
        Route::get('places/category/{category}', [PlaceController::class, 'byCategory']);

        // Reviews de un lugar
        Route::get('places/{place}/reviews', fn($placeId) => \App\Models\Review::where('place_id', $placeId)->get());

        // ─────────────────────────────────────────────────────────────
        // 📅 EVENTOS - Ahora protegidos (antes públicos)
        // ─────────────────────────────────────────────────────────────
        Route::get('events', [EventController::class, 'index']);
        Route::get('events/featured', [EventController::class, 'featured']);
        Route::get('events/upcoming', [EventController::class, 'upcoming']);
        Route::get('events/ongoing', [EventController::class, 'ongoing']);
        Route::get('events/place/{placeId}', [EventController::class, 'byPlace']);
        Route::get('events/{id}', [EventController::class, 'show']);

        // ─────────────────────────────────────────────────────────────
        // 📢 PUBLICIDAD - Ahora protegido (antes público)
        // ─────────────────────────────────────────────────────────────
        Route::get('advertisements', [AdvertisementController::class, 'index']);
        Route::get('advertisements/position/{position}', [AdvertisementController::class, 'byPosition']);
        Route::get('advertisements/banners', [AdvertisementController::class, 'banners']);
        Route::get('advertisements/{id}', [AdvertisementController::class, 'show']);
        Route::post('advertisements/{id}/click', [AdvertisementController::class, 'trackClick']);

        // ─────────────────────────────────────────────────────────────
        // 🏷️ CATEGORÍAS, 🖼️ IMAGENES, ⭐ REVIEWS - Solo lectura
        // ─────────────────────────────────────────────────────────────
        Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
        Route::apiResource('images', ImageController::class)->only(['index', 'show']);
        Route::apiResource('reviews', ReviewController::class)->only(['index', 'show']);

        // ─────────────────────────────────────────────────────────────
        // 🧡 FAVORITOS
        // ─────────────────────────────────────────────────────────────
        Route::get('favorites', fn() => auth()->user()->favoritePlaces);

        Route::post('places/{place}/favorite', function ($placeId) {
            auth()->user()->favoritePlaces()->syncWithoutDetaching([$placeId]);
            return response()->json(['message' => 'Lugar agregado a favoritos.']);
        });

        Route::delete('places/{place}/favorite', function ($placeId) {
            auth()->user()->favoritePlaces()->detach($placeId);
            return response()->json(['message' => 'Lugar eliminado de favoritos.']);
        });
    });


    // ═══════════════════════════════════════════════════════════════
    // ✏️ RUTAS PARA EDITOR Y ADMIN
    // CRUD completo - Sin cambios respecto a la versión anterior
    // ═══════════════════════════════════════════════════════════════
    Route::middleware(['role:editor|admin'])->group(function () {
        
        // 📍 Lugares - CRUD
        Route::apiResource('places', PlaceController::class)->except(['index', 'show']);
        
        // 🏷️ Categorías - CRUD
        Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
        
        // 🖼️ Imágenes - CRUD
        Route::apiResource('images', ImageController::class)->except(['index', 'show']);
        
        // ⭐ Reviews - CRUD
        Route::apiResource('reviews', ReviewController::class)->except(['index', 'show']);
        
        // 📅 Eventos - Crear y actualizar
        Route::post('events', [EventController::class, 'store']);
        Route::put('events/{id}', [EventController::class, 'update']);
        Route::patch('events/{id}', [EventController::class, 'update']);
        
        // 📢 Publicidad - Crear y actualizar
        Route::post('advertisements', [AdvertisementController::class, 'store']);
        Route::put('advertisements/{id}', [AdvertisementController::class, 'update']);
        Route::patch('advertisements/{id}', [AdvertisementController::class, 'update']);
    });


    // ═══════════════════════════════════════════════════════════════
    // 🛠️ RUTAS SOLO PARA ADMIN
    // Eliminación y registro de usuarios - Sin cambios
    // ═══════════════════════════════════════════════════════════════
    Route::middleware(['role:admin'])->group(function () {
        
        // 📍 Lugares - Eliminar
        Route::delete('places/{place}', [PlaceController::class, 'destroy']);
        
        // 🏷️ Categorías - Eliminar
        Route::delete('categories/{category}', [CategoryController::class, 'destroy']);
        
        // 🖼️ Imágenes - Eliminar
        Route::delete('images/{image}', [ImageController::class, 'destroy']);
        
        // ⭐ Reviews - Eliminar
        Route::delete('reviews/{review}', [ReviewController::class, 'destroy']);
        
        // 📅 Eventos - Eliminar
        Route::delete('events/{id}', [EventController::class, 'destroy']);
        
        // 📢 Publicidad - Eliminar
        Route::delete('advertisements/{id}', [AdvertisementController::class, 'destroy']);

        // Registro con rol explícito (solo admin puede crear otros admins/editors)
        Route::post('admin/register', [AuthController::class, 'registerWithRole']);
    });
});
