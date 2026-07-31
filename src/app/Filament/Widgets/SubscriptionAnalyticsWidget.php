<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\OwnerSubscription;
use App\Models\SubscriptionPlan;

class SubscriptionAnalyticsWidget extends BaseWidget
{
    protected ?string $heading = 'Analítica de Suscripciones';

    protected function getStats(): array
    {
        $activeSubs = OwnerSubscription::where('status', 'active')->count();
        $expiredSubs = OwnerSubscription::where('status', 'expired')->count();
        $pendingSubs = OwnerSubscription::where('status', 'pending')->count();

        $monthlyRevenue = OwnerSubscription::where('status', 'active')
            ->where('payment_status', 'paid')
            ->join('subscription_plans', 'owner_subscriptions.subscription_plan_id', '=', 'subscription_plans.id')
            ->selectRaw('SUM(subscription_plans.price_monthly) as mrr')
            ->value('mrr') ?? 0;

        $yearlyRevenue = OwnerSubscription::where('status', 'active')
            ->where('payment_status', 'paid')
            ->join('subscription_plans', 'owner_subscriptions.subscription_plan_id', '=', 'subscription_plans.id')
            ->selectRaw('SUM(subscription_plans.price_yearly) as arr')
            ->value('arr') ?? 0;

        $churnRate = ($activeSubs + $expiredSubs) > 0 ? ($expiredSubs / ($activeSubs + $expiredSubs)) * 100 : 0;

        return [
            Stat::make('MRR (Ingreso Mensual)', '$' . number_format($monthlyRevenue, 2))
                ->description('Suscripciones activas mensuales')
                ->color('success'),
            Stat::make('ARR (Ingreso Anual)', '$' . number_format($yearlyRevenue, 2))
                ->description('Suscripciones anuales')
                ->color('success'),
            Stat::make('Suscripciones Activas', $activeSubs)
                ->description('Total activas')
                ->color('success'),
            Stat::make('Tasa de Cancelación', number_format($churnRate, 1) . '%')
                ->description('Suscripciones expiradas')
                ->color($churnRate > 20 ? 'danger' : 'warning'),
        ];
    }
}
