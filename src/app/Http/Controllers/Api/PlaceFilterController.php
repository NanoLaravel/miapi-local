<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Place;

class PlaceFilterController extends Controller
{
    public function anyFacility(Request $request)
    {
        $facilities = $request->query('facilities');
        if (!$facilities) {
            return response()->json(['error' => 'Debe especificar al menos una comodidad (facilities)'], 400);
        }

        $facilitiesArray = array_map(fn($f) => strtolower(trim($f)), explode(',', $facilities));

        $places = Place::where(function($query) use ($facilitiesArray) {
            foreach ($facilitiesArray as $facility) {
                $query->orWhereJsonContains('facilities', $facility);
            }
        })
        ->with(['categories', 'images', 'reviews'])
        ->get();

        return $places;
    }

    public function facilities(Request $request)
    {
        $facilities = $request->query('facilities');
        if (!$facilities) {
            return response()->json(['error' => 'Debe especificar al menos una comodidad (facilities)'], 400);
        }

        $facilitiesArray = explode(',', $facilities);

        $places = Place::where(function($query) use ($facilitiesArray) {
            foreach ($facilitiesArray as $facility) {
                $query->whereJsonContains('facilities', $facility);
            }
        })
        ->with(['categories', 'images', 'reviews'])
        ->get();

        return $places;
    }

    public function bestReviews()
    {
        return Place::with(['categories', 'images', 'reviews'])
            ->withAvg('reviews', 'rating')
            ->orderByDesc('reviews_avg_rating')
            ->take(10)
            ->get();
    }

    public function nearby(Request $request)
    {
        $lat = $request->query('lat');
        $lng = $request->query('lng');
        $radius = $request->query('radius', 5);

        if (!$lat || !$lng) {
            return response()->json(['error' => 'lat y lng son requeridos'], 400);
        }

        $places = Place::whereNotNull('latitude')
            ->whereNotNull('longitude')
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
    }

    public function filterByType($type)
    {
        return Place::where('type', $type)
            ->with(['categories', 'images', 'reviews'])
            ->get();
    }

    public function filterByCategory($categoryId)
    {
        return Place::whereHas('categories', fn($q) => $q->where('categories.id', $categoryId))
            ->with(['categories', 'images', 'reviews'])
            ->get();
    }

    public function searchByName($name)
    {
        return Place::where('name', 'like', "%$name%")
            ->with(['categories', 'images', 'reviews'])
            ->get();
    }

    public function ratingRange($min, $max)
    {
        return Place::whereBetween('rating', [$min, $max])
            ->with(['categories', 'images', 'reviews'])
            ->get();
    }

    // USUARIO AUTENTICADO -> Favoritos
    public function favorites()
    {
        $user = auth()->user();
        return $user->favoritePlaces()
            ->with(['categories', 'images', 'reviews'])
            ->get();
    }
}

