<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Place;

class PlaceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Place::query();
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        if ($request->has('category_id')) {
            $query->whereHas('categories', function($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }
        return $query->with(['categories', 'images', 'reviews'])->paginate(10);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Place::with(['categories', 'images', 'reviews'])->findOrFail($id);
    }

    /**
     * Display a listing of the resource by type.
     */
    public function byType($type)
    {
        return Place::where('type', $type)->with(['categories', 'images', 'reviews'])->get();
    }

    /**
     * Display a listing of the resource by category.
     */
    public function byCategory($categoryId)
    {
        return Place::whereHas('categories', function($q) use ($categoryId) {
            $q->where('categories.id', $categoryId);
        })->with(['categories', 'images', 'reviews'])->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
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
            'facilities' => 'nullable', // Puede ser string o array
        ]);

        // Normalizar facilities a array si viene como string
        if (isset($validated['facilities']) && is_string($validated['facilities'])) {
            $validated['facilities'] = array_map('trim', explode(',', $validated['facilities']));
        }

        $place = Place::create($validated);

        // Asociar categorías si se envían
        if ($request->has('category_ids')) {
            $place->categories()->sync($request->input('category_ids'));
        }

        return response()->json($place->load(['categories', 'images', 'reviews']), 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'sometimes|required|string|max:255',
            'latitude' => 'sometimes|required|numeric',
            'longitude' => 'sometimes|required|numeric',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'type' => 'sometimes|required|in:restaurant,hotel,recreation,other',
            'category_ids' => 'array',
            'category_ids.*' => 'exists:categories,id',
            'facilities' => 'nullable', // Puede ser string o array
        ]);

        if (isset($validated['facilities']) && is_string($validated['facilities'])) {
            $validated['facilities'] = array_map('trim', explode(',', $validated['facilities']));
        }

        $place = Place::findOrFail($id);
        $place->update($validated);

        // Actualizar categorías si se envían
        if ($request->has('category_ids')) {
            $place->categories()->sync($request->input('category_ids'));
        }

        return response()->json($place->load(['categories', 'images', 'reviews']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $place = Place::findOrFail($id);
        $place->categories()->detach();
        $place->images()->delete();
        $place->reviews()->delete();
        $place->delete();
        return response()->json(['message' => 'Lugar eliminado correctamente.']);
    }
}
