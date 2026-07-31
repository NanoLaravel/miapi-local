<?php

namespace App\Http\Controllers\Api;

use App\Helpers\PlanAccessHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LocalProduct;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class LocalProductController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->authorizeResource(LocalProduct::class, 'local_product');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = LocalProduct::query()->with(['images', 'user:id,name']);

        $ownerId = $request->get('owner_id');
        if ($ownerId) {
            $limits = PlanAccessHelper::getPlanLimits((int) $ownerId, 'local_product');
            $request->merge(['plan_limits' => $limits]);
        }

        // Filtrar por activo
        if ($request->has('active')) {
            $query->where('is_active', filter_var($request->active, FILTER_VALIDATE_BOOLEAN));
        } else {
            // Por defecto, solo mostrar activos a menos que se especifique lo contrario
            $query->active();
        }

        // Filtrar por destacado
        if ($request->has('featured')) {
            $query->where('is_featured', filter_var($request->featured, FILTER_VALIDATE_BOOLEAN));
        }

        // Búsqueda por término (nombre, productor, descripción)
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('producer_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $items = $query->paginate($request->get('per_page', 10));

        foreach ($items as $item) {
            $item->setAttribute('plan_limits', PlanAccessHelper::getPlanLimits((int) ($item->user_id ?? 0), 'local_product'));
        }

        return $items;
    }

    /**
     * Display only active & featured local products.
     */
    public function featured()
    {
        $products = LocalProduct::active()
            ->featured()
            ->with(['images', 'user:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($products as $product) {
            $product->setAttribute('plan_limits', PlanAccessHelper::getPlanLimits((int) ($product->user_id ?? 0), 'local_product'));
        }

        return $products;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'description' => 'required|string',
            'producer_name' => 'required|string|max:255',
            'approximate_location' => 'nullable|string|max:255',
            'phone' => 'required|string|max:50',
            'facebook_url' => ['nullable', 'string', 'max:255'],
            'instagram_url' => ['nullable', 'string', 'max:255'],
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $validated['user_id'] = Auth::id();

        $product = LocalProduct::create($validated);
        $product->load(['images', 'user:id,name']);
        $product->setAttribute('plan_limits', PlanAccessHelper::getPlanLimits((int) ($product->user_id ?? 0), 'local_product'));

        return response()->json($product, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(LocalProduct $localProduct)
    {
        $localProduct->load(['images', 'user:id,name']);
        $localProduct->setAttribute('plan_limits', PlanAccessHelper::getPlanLimits((int) $localProduct->user_id, 'local_product'));

        return $localProduct;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LocalProduct $localProduct)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'description' => 'sometimes|required|string',
            'producer_name' => 'sometimes|required|string|max:255',
            'approximate_location' => 'nullable|string|max:255',
            'phone' => 'sometimes|required|string|max:50',
            'facebook_url' => ['nullable', 'string', 'max:255'],
            'instagram_url' => ['nullable', 'string', 'max:255'],
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $localProduct->update($validated);
        $localProduct->load(['images', 'user:id,name']);
        $localProduct->setAttribute('plan_limits', PlanAccessHelper::getPlanLimits((int) ($localProduct->user_id ?? 0), 'local_product'));

        return response()->json($localProduct);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LocalProduct $localProduct)
    {
        // La tabla images es polimórfica y tiene onDelete('cascade') en algunas configuraciones, 
        // pero eliminamos las imágenes asociadas manualmente para evitar archivos huérfanos.
        foreach ($localProduct->images as $image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($image->path);
            $image->delete();
        }

        $localProduct->delete();

        return response()->json(['message' => 'Producto local eliminado correctamente.']);
    }
}
