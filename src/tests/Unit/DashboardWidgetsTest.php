<?php

namespace Tests\Unit;

use App\Filament\Widgets\LeadStatusChartWidget;
use App\Filament\Widgets\MonetizationOverviewWidget;
use App\Filament\Widgets\PromotionPerformanceWidget;
use App\Filament\Widgets\PromotionUsageChartWidget;
use App\Filament\Widgets\ReservationMetricsWidget;
use App\Filament\Widgets\RevenueChartWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\SubscriptionAnalyticsWidget;
use App\Models\Lead;
use App\Models\OwnerSubscription;
use App\Models\Promotion;
use App\Models\Reservation;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardWidgetsTest extends TestCase
{
    use RefreshDatabase;

    private function invokeProtectedMethod(object $object, string $method, array $params = []): mixed
    {
        $reflection = new \ReflectionMethod($object, $method);
        $reflection->setAccessible(true);
        return $reflection->invoke($object, ...$params);
    }

    public function test_stats_overview_widget_returns_general_stats(): void
    {
        User::factory()->count(3)->create();
        SubscriptionPlan::create([
            'name' => 'Plan Test',
            'slug' => 'plan-test',
            'target_type' => 'place',
            'is_active' => true,
        ]);

        $widget = new StatsOverviewWidget();
        $stats = $this->invokeProtectedMethod($widget, 'getStats');

        $this->assertIsArray($stats);
        $this->assertGreaterThan(0, count($stats));
    }

    public function test_monetization_overview_widget_returns_stats_with_trends(): void
    {
        $widget = new MonetizationOverviewWidget();
        $stats = $this->invokeProtectedMethod($widget, 'getStats');

        $this->assertIsArray($stats);
        $this->assertGreaterThan(0, count($stats));
    }

    public function test_revenue_chart_widget_returns_monthly_data(): void
    {
        $owner = User::create([
            'name' => 'Revenue Owner',
            'email' => 'revenue-owner@test.com',
            'password' => bcrypt('secret'),
        ]);

        $plan = SubscriptionPlan::create([
            'name' => 'Revenue Plan',
            'slug' => 'revenue-plan',
            'target_type' => 'place',
            'price_monthly' => 50000,
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

        $lead = Lead::create([
            'user_id' => $owner->id,
            'owner_id' => $owner->id,
            'leadable_type' => 'place',
            'leadable_id' => 1,
            'contact_type' => 'whatsapp',
            'message' => 'Test lead',
            'status' => 'pending',
            'source' => 'app',
            'priority' => 'medium',
        ]);

        Reservation::create([
            'owner_id' => $owner->id,
            'user_id' => $owner->id,
            'lead_id' => $lead->id,
            'reservation_code' => 'REV-001',
            'total_amount' => 150000,
            'status' => 'confirmed',
            'check_in_date' => now()->addDays(5),
            'check_out_date' => now()->addDays(7),
            'nights' => 2,
        ]);

        $widget = new RevenueChartWidget();
        $data = $this->invokeProtectedMethod($widget, 'getData');

        $this->assertIsArray($data);
        $this->assertArrayHasKey('datasets', $data);
        $this->assertArrayHasKey('labels', $data);
        $this->assertCount(6, $data['labels']);
        $this->assertCount(2, $data['datasets']);
    }

    public function test_lead_status_chart_widget_returns_distribution(): void
    {
        Lead::create([
            'user_id' => User::factory()->create()->id,
            'owner_id' => User::factory()->create()->id,
            'leadable_type' => 'place',
            'leadable_id' => 1,
            'contact_type' => 'whatsapp',
            'message' => 'Test',
            'status' => 'pending',
            'source' => 'app',
            'priority' => 'medium',
        ]);

        $widget = new LeadStatusChartWidget();
        $data = $this->invokeProtectedMethod($widget, 'getData');

        $this->assertIsArray($data);
        $this->assertArrayHasKey('datasets', $data);
        $this->assertArrayHasKey('labels', $data);
        $this->assertCount(5, $data['labels']);
    }

    public function test_reservation_metrics_widget_returns_financial_metrics(): void
    {
        $widget = new ReservationMetricsWidget();
        $stats = $this->invokeProtectedMethod($widget, 'getStats');

        $this->assertIsArray($stats);
        $this->assertGreaterThan(0, count($stats));
    }

    public function test_subscription_analytics_widget_returns_mrr_and_churn(): void
    {
        $widget = new SubscriptionAnalyticsWidget();
        $stats = $this->invokeProtectedMethod($widget, 'getStats');

        $this->assertIsArray($stats);
        $this->assertGreaterThan(0, count($stats));
        $this->assertStringContainsString('MRR', $stats[0]->getLabel());
        $this->assertStringContainsString('Cancelación', $stats[3]->getLabel());
    }

    public function test_promotion_usage_chart_widget_returns_six_month_data(): void
    {
        Promotion::create([
            'owner_id' => User::factory()->create()->id,
            'name' => 'Promo 1',
            'code' => 'PROMO1',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'is_active' => true,
        ]);

        $widget = new PromotionUsageChartWidget();
        $data = $this->invokeProtectedMethod($widget, 'getData');

        $this->assertIsArray($data);
        $this->assertArrayHasKey('datasets', $data);
        $this->assertArrayHasKey('labels', $data);
        $this->assertCount(6, $data['labels']);
        $this->assertCount(2, $data['datasets']);
    }

    public function test_promotion_performance_widget_returns_performance_metrics(): void
    {
        SubscriptionPlan::create([
            'name' => 'Promo Plan',
            'slug' => 'promo-plan',
            'target_type' => 'hybrid',
            'promotions_enabled' => true,
            'is_active' => true,
        ]);

        $widget = new PromotionPerformanceWidget();
        $stats = $this->invokeProtectedMethod($widget, 'getStats');

        $this->assertIsArray($stats);
        $this->assertGreaterThan(0, count($stats));
        $this->assertStringContainsString('Promociones Activas', $stats[0]->getLabel());
        $this->assertStringContainsString('Tasa de Activación', $stats[1]->getLabel());
    }
}
