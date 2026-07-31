<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\OwnerSubscription;
use App\Models\Promotion;
use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    public function summary()
    {
        $ownerId = Auth::id();

        $subscription = \App\Helpers\PlanAccessHelper::getActiveSubscription($ownerId);

        if (!$subscription || !$subscription->plan?->analytics_enabled) {
            return response()->json([
                'message' => 'El negocio no tiene un plan activo con analytics habilitado.',
            ], 403);
        }

        $summary = [
            'data' => [
                'owner_id' => $ownerId,
                'leads_count' => Lead::query()->where('owner_id', $ownerId)->count(),
                'reservations_count' => Reservation::query()->where('owner_id', $ownerId)->count(),
                'promotions_count' => Promotion::query()->where('owner_id', $ownerId)->count(),
                'plan_name' => $subscription->plan->name,
            ],
        ];

        return response()->json($summary);
    }
}
