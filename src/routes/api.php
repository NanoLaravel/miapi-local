<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PlaceController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Log;

// Filtros personalizados de places (deben ir antes de apiResource)

// Filtro por al menos una facility (comodidad), normalizando a minúsculas y sin espacios
Route::get('places/any-facility', function (Request $request) {
    Log::info('Entrando a la ruta any-facility', ['query' => $request->query()]);
    $facilities = $request->query('facilities');
    if (!$facilities) {
        return response()->json(['error' => 'Debe especificar al menos una comodidad (facilities)'], 400);
    }
    $facilitiesArray = array_map(function($f) {
        return strtolower(trim($f));
    }, explode(',', $facilities));
    Log::info('Buscando facilities', ['facilities' => $facilitiesArray]);
    $places = \App\Models\Place::where(function($query) use ($facilitiesArray) {
        foreach ($facilitiesArray as $facility) {
            $query->orWhereJsonContains('facilities', $facility);
        }
    })
    ->with(['categories', 'images', 'reviews'])
    ->get();
    return $places;
});

// Filtro por una o varias comodidades (facilities)
Route::get('places/facilities', function (Request $request) {
    $facilities = $request->query('facilities'); // Ejemplo: piscina,parqueadero
    if (!$facilities) {
        return response()->json(['error' => 'Debe especificar al menos una comodidad (facilities)'], 400);
    }
    $facilitiesArray = explode(',', $facilities);
    $places = \App\Models\Place::where(function($query) use ($facilitiesArray) {
        foreach ($facilitiesArray as $facility) {
            $query->whereJsonContains('facilities', $facility);
        }
    })
    ->with(['categories', 'images', 'reviews'])
    ->get();
    return $places;
});

// Filtro por mejores reviews (lugares con mayor rating promedio, compatible con cualquier versión)
Route::get('places/best-reviews', function () {
    $places = \App\Models\Place::with(['categories', 'images', 'reviews'])
        ->get()
        ->map(function($place) {
            $place->reviews_avg_rating = $place->reviews->avg('rating') ?? 0;
            return $place;
        })
        ->sortByDesc('reviews_avg_rating')
        ->take(10)
        ->values();
    return $places;
});

// Filtro por ubicación aproximada (latitud/longitud y radio en km, omitiendo lugares sin coordenadas)
Route::get('places/nearby', function (Request $request) {
    $lat = $request->query('lat');
    $lng = $request->query('lng');
    $radius = $request->query('radius', 5); // km
    if (!$lat || !$lng) return response()->json(['error' => 'lat and lng required'], 400);
    $places = \App\Models\Place::whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->select('*')
        ->get()
        ->filter(function($place) use ($lat, $lng, $radius) {
            $distance = 6371 * acos(
                cos(deg2rad($lat)) * cos(deg2rad($place->latitude)) *
                cos(deg2rad($place->longitude) - deg2rad($lng)) +
                sin(deg2rad($lat)) * sin(deg2rad($place->latitude))
            );
            return $distance <= $radius;
        })
        ->values();
    return $places;
});

// Filtro por tipo
Route::get('places/filter/type/{type}', function ($type) {
    return \App\Models\Place::where('type', $type)
        ->with(['categories', 'images', 'reviews'])
        ->get();
});

// Filtro por categoría
Route::get('places/filter/category/{categoryId}', function ($categoryId) {
    return \App\Models\Place::whereHas('categories', function($q) use ($categoryId) {
        $q->where('categories.id', $categoryId);
    })
    ->with(['categories', 'images', 'reviews'])
    ->get();
});

// Filtro por nombre (búsqueda parcial)
Route::get('places/search/{name}', function ($name) {
    return \App\Models\Place::where('name', 'like', "%$name%")
        ->with(['categories', 'images', 'reviews'])
        ->get();
});

// Filtro por rango de rating
Route::get('places/rating/{min}/{max}', function ($min, $max) {
    return \App\Models\Place::whereBetween('rating', [$min, $max])
        ->with(['categories', 'images', 'reviews'])
        ->get();
});

// Filtro por favoritos de un usuario (ahora funcional)
Route::get('places/favorites/{userId}', function ($userId) {
    return \App\Models\Place::whereHas('favorites', function($q) use ($userId) {
        $q->where('user_id', $userId);
    })
    ->with(['categories', 'images', 'reviews'])
    ->get();
});

// Rutas adicionales para filtrar lugares por tipo o categoría (legacy)
Route::get('places/type/{type}', [PlaceController::class, 'byType']);
Route::get('places/category/{category}', [PlaceController::class, 'byCategory']);
Route::get('places/{place}/reviews', function ($placeId) {
    return \App\Models\Review::where('place_id', $placeId)->get();
});

// Agregar un lugar a favoritos de un usuario
Route::post('places/{place}/favorite/{user}', function ($placeId, $userId) {
    $user = \App\Models\User::findOrFail($userId);
    $user->favoritePlaces()->syncWithoutDetaching([$placeId]);
    return response()->json(['message' => 'Lugar agregado a favoritos.']);
});

// Eliminar un lugar de favoritos de un usuario
Route::delete('places/{place}/favorite/{user}', function ($placeId, $userId) {
    $user = \App\Models\User::findOrFail($userId);
    $user->favoritePlaces()->detach($placeId);
    return response()->json(['message' => 'Lugar eliminado de favoritos.']);
});

// Filtro: lugares con precio mínimo entre un rango (por tipo de precio opcional)
Route::get('places/price-range', function (Request $request) {
    $min = $request->query('min');
    $max = $request->query('max');
    $type = $request->query('type'); // opcional
    $query = \App\Models\Place::whereHas('prices', function($q) use ($min, $max, $type) {
        if ($min !== null) $q->where('value', '>=', $min);
        if ($max !== null) $q->where('value', '<=', $max);
        if ($type) $q->where('type', $type);
    });
    return $query->with(['categories', 'images', 'reviews', 'prices'])->get();
});

// Filtro: lugares con al menos un precio de tipo específico
Route::get('places/price-type/{type}', function ($type) {
    return \App\Models\Place::whereHas('prices', function($q) use ($type) {
        $q->where('type', $type);
    })->with(['categories', 'images', 'reviews', 'prices'])->get();
});

// Endpoint combinado para filtrar lugares por múltiples criterios
Route::get('places/advanced-filter', function (Request $request) {
    $query = \App\Models\Place::query();

    // Filtro por facilities (comodidades)
    if ($request->filled('facilities')) {
        $facilitiesArray = array_map(function($f) {
            return strtolower(trim($f));
        }, explode(',', $request->query('facilities')));
        $query->where(function($q) use ($facilitiesArray) {
            foreach ($facilitiesArray as $facility) {
                $q->whereJsonContains('facilities', $facility);
            }
        });
    }

    // Filtro por tipo
    if ($request->filled('type')) {
        $query->where('type', $request->query('type'));
    }

    // Filtro por categoría
    if ($request->filled('category_id')) {
        $query->whereHas('categories', function($q) use ($request) {
            $q->where('categories.id', $request->query('category_id'));
        });
    }

    // Filtro por rango de precio y tipo de precio
    if ($request->filled('min_price') || $request->filled('max_price') || $request->filled('price_type')) {
        $query->whereHas('prices', function($q) use ($request) {
            if ($request->filled('min_price')) $q->where('value', '>=', $request->query('min_price'));
            if ($request->filled('max_price')) $q->where('value', '<=', $request->query('max_price'));
            if ($request->filled('price_type')) $q->where('type', $request->query('price_type'));
        });
    }

    // Filtro por ubicación (lat, lng, radius en km)
    if ($request->filled('lat') && $request->filled('lng') && $request->filled('radius')) {
        $lat = $request->query('lat');
        $lng = $request->query('lng');
        $radius = $request->query('radius');
        $query->whereNotNull('latitude')->whereNotNull('longitude');
        $places = $query->get()->filter(function($place) use ($lat, $lng, $radius) {
            $distance = 6371 * acos(
                cos(deg2rad($lat)) * cos(deg2rad($place->latitude)) *
                cos(deg2rad($place->longitude) - deg2rad($lng)) +
                sin(deg2rad($lat)) * sin(deg2rad($place->latitude))
            );
            return $distance <= $radius;
        })->values();
        return $places->load(['categories', 'images', 'reviews', 'prices']);
    }

    return $query->with(['categories', 'images', 'reviews', 'prices'])->get();
});

// Rutas de autenticación
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Socialite Google
Route::get('auth/google/redirect', [AuthController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

// Socialite Facebook
Route::get('auth/facebook/redirect', [AuthController::class, 'redirectToFacebook']);
Route::get('auth/facebook/callback', [AuthController::class, 'handleFacebookCallback']);

// Registro de usuarios
Route::post('register', [AuthController::class, 'register']);

Route::apiResource('places', PlaceController::class);
Route::apiResource('categories', CategoryController::class);
Route::apiResource('images', ImageController::class);
Route::apiResource('reviews', ReviewController::class);
