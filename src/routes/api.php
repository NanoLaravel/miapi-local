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

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS
|--------------------------------------------------------------------------
|
| Únicamente autenticación.
| Todo el contenido turístico requiere login.
|
*/

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'registerPublic']);

// OAuth
Route::get('auth/google/redirect', [AuthController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

Route::get('auth/facebook/redirect', [AuthController::class, 'redirectToFacebook']);
Route::get('auth/facebook/callback', [AuthController::class, 'handleFacebookCallback']);


/*
|--------------------------------------------------------------------------
| RUTAS PROTEGIDAS
|--------------------------------------------------------------------------
|
| Todas requieren Sanctum.
| La autorización fina se realiza mediante Policies.
|
*/

Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | AUTENTICACIÓN
    |--------------------------------------------------------------------------
    */

    Route::post('logout', [AuthController::class, 'logout']);


    /*
    |--------------------------------------------------------------------------
    | RECURSOS PRINCIPALES
    |--------------------------------------------------------------------------
    |
    | Los permisos se controlan desde:
    | - Policies
    | - authorizeResource()
    |
    */

    Route::apiResource('places', PlaceController::class);

    Route::apiResource('categories', CategoryController::class);

    Route::apiResource('images', ImageController::class);

    Route::apiResource('reviews', ReviewController::class);

    Route::apiResource('events', EventController::class);

    Route::apiResource('advertisements', AdvertisementController::class);


    /*
    |--------------------------------------------------------------------------
    | FILTROS DE LUGARES
    |--------------------------------------------------------------------------
    */

    Route::get(
        'places/filter/type/{type}',
        [PlaceFilterController::class, 'filterByType']
    );

    Route::get(
        'places/filter/category/{categoryId}',
        [PlaceFilterController::class, 'filterByCategory']
    );

    Route::get(
        'places/search/{name}',
        [PlaceFilterController::class, 'searchByName']
    );

    Route::get(
        'places/rating/{min}/{max}',
        [PlaceFilterController::class, 'ratingRange']
    );

    Route::get(
        'places/best-reviews',
        [PlaceFilterController::class, 'bestReviews']
    );

    Route::get(
        'places/any-facility',
        [PlaceFilterController::class, 'anyFacility']
    );

    Route::get(
        'places/type/{type}',
        [PlaceController::class, 'byType']
    );

    Route::get(
        'places/category/{category}',
        [PlaceController::class, 'byCategory']
    );


    /*
    |--------------------------------------------------------------------------
    | EVENTOS
    |--------------------------------------------------------------------------
    */

    Route::get(
        'events/featured',
        [EventController::class, 'featured']
    );

    Route::get(
        'events/upcoming',
        [EventController::class, 'upcoming']
    );

    Route::get(
        'events/ongoing',
        [EventController::class, 'ongoing']
    );

    Route::get(
        'events/place/{placeId}',
        [EventController::class, 'byPlace']
    );


    /*
    |--------------------------------------------------------------------------
    | PUBLICIDAD
    |--------------------------------------------------------------------------
    */

    Route::get(
        'advertisements/position/{position}',
        [AdvertisementController::class, 'byPosition']
    );

    Route::get(
        'advertisements/banners',
        [AdvertisementController::class, 'banners']
    );

    Route::post(
        'advertisements/{id}/click',
        [AdvertisementController::class, 'trackClick']
    );


    /*
    |--------------------------------------------------------------------------
    | REVIEWS DE UN LUGAR
    |--------------------------------------------------------------------------
    */

    Route::get(
        'places/{place}/reviews',
        fn ($placeId) =>
            \App\Models\Review::where('place_id', $placeId)->get()
    );


    /*
    |--------------------------------------------------------------------------
    | FAVORITOS
    |--------------------------------------------------------------------------
    */

    Route::get(
        'favorites',
        fn () => auth()->user()->favoritePlaces
    );

    Route::post(
        'places/{place}/favorite',
        function ($placeId) {
            auth()->user()
                ->favoritePlaces()
                ->syncWithoutDetaching([$placeId]);

            return response()->json([
                'message' => 'Lugar agregado a favoritos.'
            ]);
        }
    );

    Route::delete(
        'places/{place}/favorite',
        function ($placeId) {
            auth()->user()
                ->favoritePlaces()
                ->detach($placeId);

            return response()->json([
                'message' => 'Lugar eliminado de favoritos.'
            ]);
        }
    );


    /*
    |--------------------------------------------------------------------------
    | ADMINISTRACIÓN DE USUARIOS
    |--------------------------------------------------------------------------
    |
    | Aquí todavía podrías mantener middleware role:admin
    | porque no existe un UserPolicy para esta ruta.
    |
    */

    Route::middleware('role:admin|super_admin')->group(function () {

        Route::post(
            'admin/register',
            [AuthController::class, 'registerWithRole']
        );

    });

});