<?php

namespace Tests\Feature;

use App\Models\LocalProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LocalProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_list_local_products(): void
    {
        $user = User::factory()->create();
        Role::firstOrCreate(['name' => 'user']);
        $user->assignRole('user');

        $product = LocalProduct::create([
            'name' => 'Café de la montaña',
            'price' => 15000,
            'description' => 'Café especial de la región.',
            'producer_name' => 'Productor test',
            'approximate_location' => 'La Mesa',
            'phone' => '3001234567',
            'facebook_url' => 'https://facebook.com/test',
            'instagram_url' => 'https://instagram.com/test',
            'is_active' => true,
            'is_featured' => true,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/local-products');

        $response->assertOk()
            ->assertJsonFragment([
                'name' => $product->name,
            ])
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'name',
                    'whatsapp_url',
                    'first_image_url',
                ]],
            ]);
    }

    public function test_unauthenticated_user_cannot_access_local_products(): void
    {
        $response = $this->getJson('/api/local-products');

        $response->assertUnauthorized();
    }

    public function test_local_products_include_plan_limits_metadata(): void
    {
        $user = User::factory()->create();
        Role::firstOrCreate(['name' => 'user']);
        $user->assignRole('user');

        LocalProduct::create([
            'name' => 'Miel de la sierra',
            'price' => 25000,
            'description' => 'Miel artesanal.',
            'producer_name' => 'Productor test',
            'approximate_location' => 'La Mesa',
            'phone' => '3001234567',
            'facebook_url' => 'https://facebook.com/test',
            'instagram_url' => 'https://instagram.com/test',
            'is_active' => true,
            'is_featured' => true,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/local-products');

        $response->assertOk()
            ->assertJsonPath('data.0.plan_limits.allow_leads', false)
            ->assertJsonStructure([
                'data' => [[
                    'plan_limits' => [
                        'max_images',
                        'show_contact_button',
                        'allow_leads',
                        'allow_reservations',
                        'show_whatsapp_only',
                    ],
                ]],
            ]);
    }
}
