<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Review::query();

        // Filtros por campos individuales
        foreach ([
            'cleanliness', 'accuracy', 'check_in', 'communication', 'location', 'price', 'rating', 'place_id', 'user_id'
        ] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }

        // Filtro avanzado por promedio de ítems calificados
        if ($request->filled('items_average_min')) {
            $min = floatval($request->input('items_average_min'));
            $query->get()->filter(function($review) use ($min) {
                return $review->items_average !== null && $review->items_average >= $min;
            });
            // NOTA: Este filtro se aplica en memoria por ser un campo calculado
            return $query->get()->filter(function($review) use ($min) {
                return $review->items_average !== null && $review->items_average >= $min;
            })->values();
        }

        return $query->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'place_id' => 'required|exists:places,id',
            'user_id' => 'required|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'cleanliness' => 'nullable|integer|min:1|max:5',
            'accuracy' => 'nullable|integer|min:1|max:5',
            'check_in' => 'nullable|integer|min:1|max:5',
            'communication' => 'nullable|integer|min:1|max:5',
            'location' => 'nullable|integer|min:1|max:5',
            'price' => 'nullable|integer|min:1|max:5',
        ]);
        $review = Review::create($validated);
        return response()->json($review, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Review::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $review = Review::findOrFail($id);
        $validated = $request->validate([
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'cleanliness' => 'nullable|integer|min:1|max:5',
            'accuracy' => 'nullable|integer|min:1|max:5',
            'check_in' => 'nullable|integer|min:1|max:5',
            'communication' => 'nullable|integer|min:1|max:5',
            'location' => 'nullable|integer|min:1|max:5',
            'price' => 'nullable|integer|min:1|max:5',
        ]);
        $review->update($validated);
        return response()->json($review);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $review = Review::findOrFail($id);
        $review->delete();
        return response()->json(['message' => 'Reseña eliminada correctamente.']);
    }
}
