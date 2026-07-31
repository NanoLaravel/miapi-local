<?php

namespace Tests\Feature;

use App\Filament\Resources\LeadResource;
use App\Filament\Resources\OwnerSubscriptionResource;
use App\Filament\Resources\PromotionResource;
use App\Filament\Resources\ReservationResource;
use App\Filament\Resources\SubscriptionPlanResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MonetizationAdminPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin']);
    }

    public function test_admin_can_access_monetization_resource_pages(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(SubscriptionPlanResource::getUrl())
            ->assertStatus(200);
    }

    public function test_admin_can_access_owner_subscriptions_page(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(OwnerSubscriptionResource::getUrl())
            ->assertStatus(200);
    }

    public function test_admin_can_access_leads_page(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(LeadResource::getUrl())
            ->assertStatus(200);
    }

    public function test_admin_can_access_reservations_page(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(ReservationResource::getUrl())
            ->assertStatus(200);
    }

    public function test_admin_can_access_promotions_page(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(PromotionResource::getUrl())
            ->assertStatus(200);
    }
}
