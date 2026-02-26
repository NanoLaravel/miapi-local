<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Advertisement;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AdvertisementController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display active advertisements by position.
     * Público: muestra anuncios activos para una posición
     */
    public function index(Request $request)
    {
        $query = Advertisement::query()->with(['place:id,name', 'user:id,name']);

        // Filtrar por posición
        if ($request->has('position')) {
            $query->byPosition($request->position);
        }

        // Filtrar por tipo
        if ($request->has('type')) {
            $query->byType($request->type);
        }

        // Solo anuncios activos y vigentes
        if ($request->get('active_only', true)) {
            $query->active();
        }

        // Ordenar por prioridad
        $query->orderedByPriority();

        return $query->paginate($request->get('per_page', 10));
    }

    /**
     * Get advertisements for a specific position.
     * Público: obtiene anuncios para mostrar en la app
     */
    public function byPosition($position)
    {
        $ads = Advertisement::with(['place:id,name'])
            ->active()
            ->byPosition($position)
            ->orderedByPriority()
            ->get();

        // Incrementar vistas
        foreach ($ads as $ad) {
            $ad->incrementViews();
        }

        return $ads;
    }

    /**
     * Get banner advertisements.
     * Público: banners para la app
     */
    public function banners(Request $request)
    {
        $position = $request->get('position', 'all');
        
        return Advertisement::with(['place:id,name'])
            ->active()
            ->byType(Advertisement::TYPE_BANNER)
            ->byPosition($position)
            ->orderedByPriority()
            ->get();
    }

    /**
     * Display the specified advertisement.
     * Público: detalle de un anuncio
     */
    public function show(string $id)
    {
        $ad = Advertisement::with(['place', 'user:id,name'])->findOrFail($id);
        
        // Incrementar vistas
        $ad->incrementViews();

        return $ad;
    }

    /**
     * Track click on advertisement.
     * Público: registra un clic en el anuncio
     */
    public function trackClick(string $id)
    {
        $ad = Advertisement::findOrFail($id);
        $ad->incrementClicks();

        return response()->json([
            'message' => 'Click registrado',
            'link_url' => $ad->link_url,
        ]);
    }

    /**
     * Store a newly created advertisement.
     * Requiere rol: editor, admin
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_path' => 'required|image|max:5120', // 5MB max
            'link_url' => 'nullable|url|max:255',
            'type' => 'required|in:banner,popup,sidebar,inline',
            'position' => 'required|in:home,places,events,all',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
            'priority' => 'integer|min:0|max:100',
            'place_id' => 'nullable|exists:places,id',
        ]);

        // Manejar subida de imagen
        if ($request->hasFile('image_path')) {
            $validated['image_path'] = $request->file('image_path')
                ->store('publicidad', 'public');
        }

        // Asignar usuario autenticado como creador
        $validated['user_id'] = $request->user()->id;

        $ad = Advertisement::create($validated);

        return response()->json(
            $ad->load(['place', 'user:id,name']),
            201
        );
    }

    /**
     * Update the specified advertisement.
     * Requiere rol: editor, admin (o ser el creador)
     */
    public function update(Request $request, string $id)
    {
        $ad = Advertisement::findOrFail($id);

        // Verificar autorización
        $this->authorize('update', $ad);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'image_path' => 'nullable|image|max:5120',
            'link_url' => 'nullable|url|max:255',
            'type' => 'sometimes|required|in:banner,popup,sidebar,inline',
            'position' => 'sometimes|required|in:home,places,events,all',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after:start_date',
            'is_active' => 'boolean',
            'priority' => 'integer|min:0|max:100',
            'place_id' => 'nullable|exists:places,id',
        ]);

        // Manejar subida de imagen
        if ($request->hasFile('image_path')) {
            // Eliminar imagen anterior
            if ($ad->image_path) {
                Storage::disk('public')->delete($ad->image_path);
            }
            $validated['image_path'] = $request->file('image_path')
                ->store('publicidad', 'public');
        }

        $ad->update($validated);

        return response()->json($ad->load(['place', 'user:id,name']));
    }

    /**
     * Remove the specified advertisement.
     * Requiere rol: admin
     */
    public function destroy(string $id)
    {
        $ad = Advertisement::findOrFail($id);

        // Eliminar imagen
        if ($ad->image_path) {
            Storage::disk('public')->delete($ad->image_path);
        }

        $ad->delete();

        return response()->json(['message' => 'Anuncio eliminado correctamente.']);
    }
}
