<?php

namespace Tests\Feature;

use App\Models\OwnerSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_with_active_plan_can_access_analytics_summary(): void
    {
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner-analytics@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $plan = SubscriptionPlan::create([
            'name' => 'Analytics Pro',
            'slug' => 'analytics-pro',
            'description' => 'Plan con analytics',
            'target_type' => 'hybrid',
            'price_monthly' => 40000,
            'lead_limit' => 100,
            'analytics_enabled' => true,
            'promotions_enabled' => true,
            'is_active' => true,
        ]);

        OwnerSubscription::create([
            'owner_id' => $owner->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'started_at' => now(),
            'ends_at' => now()->addMonth(),
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($owner, 'sanctum')->getJson('/api/analytics/summary');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'owner_id',
                    'leads_count',
                    'reservations_count',
                    'promotions_count',
                    'plan_name',
                ],
            ]);
    }

    public function test_free_plan_cannot_access_analytics_summary(): void
    {
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner-free-analytics@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->actingAs($owner, 'sanctum')->getJson('/api/analytics/summary');

        $response->assertStatus(403)
            ->assertJsonFragment([
                'message' => 'El negocio no tiene un plan activo con analytics habilitado.',
            ]);
    }
}
