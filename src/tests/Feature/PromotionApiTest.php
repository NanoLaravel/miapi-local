<?php

namespace Tests\Feature;

use App\Models\OwnerSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_with_active_plan_can_create_a_promotion(): void
    {
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner-promo@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $plan = SubscriptionPlan::create([
            'name' => 'Basic Promotions',
            'slug' => 'basic-promotions',
            'description' => 'Plan con promociones',
            'target_type' => 'hybrid',
            'price_monthly' => 30000,
            'lead_limit' => 50,
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

        $response = $this->actingAs($owner, 'sanctum')->postJson('/api/promotions', [
            'name' => 'Promo verano',
            'code' => 'VERANO10',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addWeek()->toDateString(),
            'is_active' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Promo verano')
            ->assertJsonPath('data.code', 'VERANO10');
    }

    public function test_free_plan_cannot_create_promotions(): void
    {
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner-free-promo@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->actingAs($owner, 'sanctum')->postJson('/api/promotions', [
            'name' => 'Promo gratis',
            'code' => 'GRATIS',
            'discount_type' => 'fixed',
            'discount_value' => 5000,
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addWeek()->toDateString(),
            'is_active' => true,
        ]);

        $response->assertStatus(403)
            ->assertJsonFragment([
                'message' => 'El negocio no tiene un plan activo que permita crear promociones.',
            ]);
    }
}
