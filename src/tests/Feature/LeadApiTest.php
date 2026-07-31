<?php

namespace Tests\Feature;

use App\Models\LocalProduct;
use App\Models\OwnerSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_with_active_plan_can_create_a_lead_for_local_product(): void
    {
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $user = User::create([
            'name' => 'Visitor',
            'email' => 'visitor@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $plan = SubscriptionPlan::create([
            'name' => 'Basic',
            'slug' => 'basic-local-product',
            'description' => 'Plan base',
            'target_type' => 'local_product',
            'price_monthly' => 10000,
            'lead_limit' => 10,
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

        $product = LocalProduct::create([
            'name' => 'Miel artesanal',
            'description' => 'Producto local premium',
            'producer_name' => 'La Finca',
            'phone' => '3001234567',
            'user_id' => $owner->id,
            'is_active' => true,
            'is_featured' => false,
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/leads', [
            'owner_id' => $owner->id,
            'leadable_type' => 'local_product',
            'leadable_id' => $product->id,
            'contact_type' => 'whatsapp',
            'message' => 'Estoy interesado en este producto',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.owner_id', $owner->id)
            ->assertJsonPath('data.leadable_type', 'local_product');
    }

    public function test_free_plan_cannot_create_leads_for_local_products(): void
    {
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner2@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $user = User::create([
            'name' => 'Visitor',
            'email' => 'visitor2@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $product = LocalProduct::create([
            'name' => 'Artesanía',
            'description' => 'Producto local',
            'producer_name' => 'Cultura Viva',
            'phone' => '3007654321',
            'user_id' => $owner->id,
            'is_active' => true,
            'is_featured' => false,
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/leads', [
            'owner_id' => $owner->id,
            'leadable_type' => 'local_product',
            'leadable_id' => $product->id,
            'contact_type' => 'whatsapp',
            'message' => 'Quiero saber más',
        ]);

        $response->assertStatus(403)
            ->assertJsonFragment([
                'message' => 'El negocio no tiene un plan activo que permita recibir leads.',
            ]);
    }
}
