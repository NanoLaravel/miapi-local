<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Image;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Image::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Log::info('Request recibido en store', [
            'all' => $request->all(),
            'hasFile' => $request->hasFile('image'),
            'file' => $request->file('image')
        ]);
        $validated = $request->validate([
            'place_id' => 'required|exists:places,id',
            'image' => 'required|image|max:2048',
            'description' => 'nullable|string|max:255',
        ]);

        $file = $request->file('image');
        $originalName = $file->getClientOriginalName();
        $path = $file->storeAs('lugares', $originalName, 'public');

        $image = Image::create([
            'place_id' => $validated['place_id'],
            'path' => $path,
            'description' => $validated['description'] ?? null,
        ]);

        \Log::info('Imagen creada', ['image' => $image]);
        return response()->json($image, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Image::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $image = Image::findOrFail($id);
        $validated = $request->validate([
            'description' => 'nullable|string|max:255',
        ]);
        $image->update($validated);
        return response()->json($image);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $image = Image::findOrFail($id);
        // Eliminar archivo físico si existe
        if ($image->path && \Storage::disk('public')->exists($image->path)) {
            \Storage::disk('public')->delete($image->path);
        }
        $image->delete();
        return response()->json(['message' => 'Imagen eliminada correctamente.']);
    }
}
