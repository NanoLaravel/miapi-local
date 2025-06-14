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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
