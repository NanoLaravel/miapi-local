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

// RUTAS PÚBLICAS (sin login)
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'registerPublic']);

Route::get('places/type/{type}', [PlaceController::class, 'byType']);
Route::get('places/category/{category}', [PlaceController::class, 'byCategory']);
Route::get('places/{place}/reviews', fn($placeId) => \App\Models\Review::where('place_id', $placeId)->get());

Route::apiResource('places', PlaceController::class)->only(['index', 'show']);
Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
Route::apiResource('images', ImageController::class)->only(['index', 'show']);
Route::apiResource('reviews', ReviewController::class)->only(['index', 'show']);

// AUTENTICACIÓN SOCIAL
Route::get('auth/google/redirect', [AuthController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
Route::get('auth/facebook/redirect', [AuthController::class, 'redirectToFacebook']);
Route::get('auth/facebook/callback', [AuthController::class, 'handleFacebookCallback']);

// RUTAS PROTEGIDAS POR AUTH
Route::middleware(['auth:sanctum'])->group(function () {

    Route::post('logout', [AuthController::class, 'logout']);

    // USER: filtros avanzados, favoritos
    Route::middleware(['role:user|editor|admin'])->group(function () {
        Route::get('places/filter/type/{type}', [PlaceFilterController::class, 'filterByType']);
        Route::get('places/filter/category/{categoryId}', [PlaceFilterController::class, 'filterByCategory']);
        Route::get('places/search/{name}', [PlaceFilterController::class, 'searchByName']);
        Route::get('places/rating/{min}/{max}', [PlaceFilterController::class, 'ratingRange']);
        Route::get('places/favorites', fn() => auth()->user()->favoritePlaces);
        Route::get('places/best-reviews', [PlaceFilterController::class, 'bestReviews']);
        Route::get('places/any-facility', [PlaceFilterController::class, 'anyFacility']);

        // Favoritos sin usar ID de usuario
        Route::post('places/{place}/favorite', function ($placeId) {
            $user = auth()->user();
            $user->favoritePlaces()->syncWithoutDetaching([$placeId]);
            return response()->json(['message' => 'Lugar agregado a favoritos.']);
        });

        Route::delete('places/{place}/favorite', function ($placeId) {
            $user = auth()->user();
            $user->favoritePlaces()->detach($placeId);
            return response()->json(['message' => 'Lugar eliminado de favoritos.']);
        });
    });

    // EDITOR Y ADMIN: crear y editar recursos
    Route::middleware(['role:editor|admin'])->group(function () {
        Route::apiResource('places', PlaceController::class)->except(['index', 'show']);
        Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
        Route::apiResource('images', ImageController::class)->except(['index', 'show']);
        Route::apiResource('reviews', ReviewController::class)->except(['index', 'show']);
    });

    // ADMIN: eliminar y registrar con rol
    Route::middleware(['role:admin'])->group(function () {
        Route::delete('places/{place}', [PlaceController::class, 'destroy']);
        Route::delete('categories/{category}', [CategoryController::class, 'destroy']);
        Route::delete('images/{image}', [ImageController::class, 'destroy']);
        Route::delete('reviews/{review}', [ReviewController::class, 'destroy']);
        Route::post('admin/register', [AuthController::class, 'registerWithRole']);
    });
});
