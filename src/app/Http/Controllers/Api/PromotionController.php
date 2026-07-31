<?php

namespace App\Http\Controllers\Api;

use App\Helpers\PlanAccessHelper;
use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        $promotions = Promotion::query()
            ->where('owner_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return response()->json($promotions);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        if (!PlanAccessHelper::canCreatePromotions((int) Auth::id())) {
            return response()->json([
                'message' => 'El negocio no tiene un plan activo que permita crear promociones.',
            ], 403);
        }

        $promotion = Promotion::create([
            'owner_id' => Auth::id(),
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'message' => 'Promoción creada correctamente.',
            'data' => $promotion,
        ], 201);
    }

    public function show(Promotion $promotion)
    {
        abort_unless($promotion->owner_id === Auth::id(), 403);

        return response()->json($promotion);
    }

    public function update(Request $request, Promotion $promotion)
    {
        abort_unless($promotion->owner_id === Auth::id(), 403);

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:50',
            'discount_type' => 'sometimes|required|in:percentage,fixed',
            'discount_value' => 'sometimes|required|numeric|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        $promotion->update($request->only([
            'name', 'code', 'discount_type', 'discount_value', 'starts_at', 'ends_at', 'is_active',
        ]));

        return response()->json($promotion);
    }
}
