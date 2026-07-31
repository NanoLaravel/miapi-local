<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\LocalProduct;
use App\Models\OwnerSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_with_active_plan_can_create_a_reservation_from_a_lead(): void
    {
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner-reservation@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $user = User::create([
            'name' => 'Visitor',
            'email' => 'visitor-reservation@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $plan = SubscriptionPlan::create([
            'name' => 'Basic Places',
            'slug' => 'basic-places',
            'description' => 'Plan para lugares',
            'target_type' => 'place',
            'price_monthly' => 20000,
            'lead_limit' => 20,
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
            'name' => 'Café especial',
            'description' => 'Producto local',
            'producer_name' => 'La Finca',
            'phone' => '3001234567',
            'user_id' => $owner->id,
            'is_active' => true,
            'is_featured' => false,
        ]);

        $lead = Lead::create([
            'user_id' => $user->id,
            'owner_id' => $owner->id,
            'leadable_type' => 'local_product',
            'leadable_id' => $product->id,
            'contact_type' => 'whatsapp',
            'message' => 'Me interesa',
            'status' => 'pending',
            'source' => 'app',
            'priority' => 'medium',
        ]);

        $response = $this->actingAs($owner, 'sanctum')->postJson('/api/reservations', [
            'lead_id' => $lead->id,
            'check_in_date' => '2026-08-10',
            'check_out_date' => '2026-08-12',
            'total_amount' => 150000,
            'notes' => 'Reserva de prueba',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('owner_id', $owner->id)
            ->assertJsonPath('lead_id', $lead->id);
    }

    public function test_free_plan_cannot_create_reservations_from_leads(): void
    {
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner-free-reservation@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $user = User::create([
            'name' => 'Visitor',
            'email' => 'visitor-free-reservation@example.com',
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

        $lead = Lead::create([
            'user_id' => $user->id,
            'owner_id' => $owner->id,
            'leadable_type' => 'local_product',
            'leadable_id' => $product->id,
            'contact_type' => 'whatsapp',
            'message' => 'Quiero reservar',
            'status' => 'pending',
            'source' => 'app',
            'priority' => 'medium',
        ]);

        $response = $this->actingAs($owner, 'sanctum')->postJson('/api/reservations', [
            'lead_id' => $lead->id,
            'check_in_date' => '2026-08-10',
            'check_out_date' => '2026-08-12',
            'total_amount' => 150000,
        ]);

        $response->assertStatus(403)
            ->assertJsonFragment([
                'message' => 'El negocio no tiene un plan activo que permita gestionar reservas.',
            ]);
    }
}
