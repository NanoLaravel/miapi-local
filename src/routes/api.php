<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    PlaceController,
    CategoryController,
    ImageController,
    ReviewController,
    AuthController,
    PlaceFilterController
};


// ─────────────────────────────────────────────
// 🔓 RUTAS PÚBLICAS (sin autenticación)
// ─────────────────────────────────────────────
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'registerPublic']);

// Solo permitir listar lugares
Route::apiResource('places', PlaceController::class)->only(['index']);

// (Opcional) Si quieres que puedan ver detalles de un lugar sin login:
// Route::apiResource('places', PlaceController::class)->only(['index', 'show']);

// Login Social (opcional)
Route::get('auth/google/redirect', [AuthController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
Route::get('auth/facebook/redirect', [AuthController::class, 'redirectToFacebook']);
Route::get('auth/facebook/callback', [AuthController::class, 'handleFacebookCallback']);

// ─────────────────────────────────────────────
// 🔐 RUTAS PROTEGIDAS CON LOGIN
// ─────────────────────────────────────────────
Route::middleware(['auth:sanctum'])->group(function () {

    // 🔸 Cierre de sesión
    Route::post('logout', [AuthController::class, 'logout']);

    // ─────────────────────────────
    // 👤 USER, EDITOR, ADMIN
    // ─────────────────────────────
    Route::middleware(['role:user|editor|admin'])->group(function () {

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

        // Categorías, Imágenes, Reviews (solo lectura)
        Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
        Route::apiResource('images', ImageController::class)->only(['index', 'show']);
        Route::apiResource('reviews', ReviewController::class)->only(['index', 'show']);

        // 🧡 Favoritos
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

    // ─────────────────────────────
    // ✏️ EDITOR y ADMIN
    // ─────────────────────────────
    Route::middleware(['role:editor|admin'])->group(function () {
        Route::apiResource('places', PlaceController::class)->except(['index', 'show']);
        Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
        Route::apiResource('images', ImageController::class)->except(['index', 'show']);
        Route::apiResource('reviews', ReviewController::class)->except(['index', 'show']);
    });

    // ─────────────────────────────
    // 🛠️ SOLO ADMIN
    // ─────────────────────────────
    Route::middleware(['role:admin'])->group(function () {
        Route::delete('places/{place}', [PlaceController::class, 'destroy']);
        Route::delete('categories/{category}', [CategoryController::class, 'destroy']);
        Route::delete('images/{image}', [ImageController::class, 'destroy']);
        Route::delete('reviews/{review}', [ReviewController::class, 'destroy']);

        // Registro con rol explícito
        Route::post('admin/register', [AuthController::class, 'registerWithRole']);
    });
});