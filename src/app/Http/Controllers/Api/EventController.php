<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Place;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class EventController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of events.
     * Público: lista eventos activos y actuales
     */
    public function index(Request $request)
    {
        $query = Event::query()->with(['place:id,name,address', 'user:id,name']);

        // Filtros opcionales
        if ($request->has('active') && $request->active) {
            $query->active();
        }

        if ($request->has('featured') && $request->featured) {
            $query->featured();
        }

        if ($request->has('upcoming') && $request->upcoming) {
            $query->upcoming();
        }

        if ($request->has('ongoing') && $request->ongoing) {
            $query->ongoing();
        }

        if ($request->has('place_id')) {
            $query->where('place_id', $request->place_id);
        }

        // Filtro por rango de fechas
        if ($request->has('from_date')) {
            $query->where('start_date', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('end_date', '<=', $request->to_date);
        }

        // Búsqueda por título
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'start_date');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($request->get('per_page', 10));
    }

    /**
     * Display featured events.
     * Público: eventos destacados
     */
    public function featured()
    {
        return Event::with(['place:id,name,address'])
            ->active()
            ->featured()
            ->current()
            ->orderBy('start_date')
            ->get();
    }

    /**
     * Display upcoming events.
     * Público: eventos próximos
     */
    public function upcoming()
    {
        return Event::with(['place:id,name,address'])
            ->active()
            ->upcoming()
            ->orderBy('start_date')
            ->paginate(10);
    }

    /**
     * Display ongoing events.
     * Público: eventos en curso
     */
    public function ongoing()
    {
        return Event::with(['place:id,name,address'])
            ->active()
            ->ongoing()
            ->orderBy('end_date')
            ->get();
    }

    /**
     * Display events by place.
     * Público: eventos de un lugar específico
     */
    public function byPlace($placeId)
    {
        return Event::with(['place:id,name,address'])
            ->where('place_id', $placeId)
            ->active()
            ->orderBy('start_date')
            ->paginate(10);
    }

    /**
     * Display the specified event.
     * Público: detalle de un evento
     */
    public function show(string $id)
    {
        return Event::with(['place', 'user:id,name'])
            ->findOrFail($id);
    }

    /**
     * Store a newly created event.
     * Requiere rol: editor, admin
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date|after:now',
            'end_date' => 'required|date|after:start_date',
            'location' => 'required|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'image_path' => 'nullable|image|max:5120', // 5MB max
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'price' => 'nullable|numeric|min:0',
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'place_id' => 'nullable|exists:places,id',
        ]);

        // Manejar subida de imagen
        if ($request->hasFile('image_path')) {
            $validated['image_path'] = $request->file('image_path')
                ->store('eventos', 'public');
        }

        // Asignar usuario autenticado como creador
        $validated['user_id'] = $request->user()->id;

        $event = Event::create($validated);

        return response()->json(
            $event->load(['place', 'user:id,name']),
            201
        );
    }

    /**
     * Update the specified event.
     * Requiere rol: editor, admin (o ser el creador)
     */
    public function update(Request $request, string $id)
    {
        $event = Event::findOrFail($id);

        // Verificar autorización
        $this->authorize('update', $event);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after:start_date',
            'location' => 'sometimes|required|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'image_path' => 'nullable|image|max:5120',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'price' => 'nullable|numeric|min:0',
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'place_id' => 'nullable|exists:places,id',
        ]);

        // Manejar subida de imagen
        if ($request->hasFile('image_path')) {
            // Eliminar imagen anterior si existe
            if ($event->image_path) {
                Storage::disk('public')->delete($event->image_path);
            }
            $validated['image_path'] = $request->file('image_path')
                ->store('eventos', 'public');
        }

        $event->update($validated);

        return response()->json($event->load(['place', 'user:id,name']));
    }

    /**
     * Remove the specified event.
     * Requiere rol: admin
     */
    public function destroy(string $id)
    {
        $event = Event::findOrFail($id);

        // Eliminar imagen si existe
        if ($event->image_path) {
            Storage::disk('public')->delete($event->image_path);
        }

        $event->delete();

        return response()->json(['message' => 'Evento eliminado correctamente.']);
    }
}
