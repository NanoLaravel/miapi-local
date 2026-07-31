<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OwnerSubscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OwnerSubscriptionController extends Controller
{
    public function index()
    {
        return response()->json(
            OwnerSubscription::query()
                ->where('owner_id', Auth::id())
                ->with('plan')
                ->orderByDesc('created_at')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
        ]);

        $plan = SubscriptionPlan::findOrFail($request->subscription_plan_id);

        $subscription = OwnerSubscription::create([
            'owner_id' => Auth::id(),
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'started_at' => now(),
            'ends_at' => now()->addMonth(),
            'payment_status' => 'paid',
        ]);

        return response()->json($subscription->load('plan'), 201);
    }
}
