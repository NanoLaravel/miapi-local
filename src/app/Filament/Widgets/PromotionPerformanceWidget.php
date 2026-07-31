<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Promotion;
use App\Models\SubscriptionPlan;

class PromotionPerformanceWidget extends BaseWidget
{
    protected ?string $heading = 'Rendimiento de Promociones';

    protected function getStats(): array
    {
        $totalPromos = Promotion::count();
        $activePromos = Promotion::where('is_active', true)->count();
        $expiredPromos = Promotion::where('is_active', false)->count();
        $plansWithPromos = SubscriptionPlan::where('promotions_enabled', true)->count();

        $activeRate = $totalPromos > 0 ? ($activePromos / $totalPromos) * 100 : 0;

        return [
            Stat::make('Promociones Activas', $activePromos)
                ->description('De ' . $totalPromos . ' totales')
                ->color('success'),
            Stat::make('Tasa de Activación', number_format($activeRate, 1) . '%')
                ->description('Promos activas vs total')
                ->color($activeRate > 60 ? 'success' : 'warning'),
            Stat::make('Planes con Promociones', $plansWithPromos)
                ->description('Planes que permiten promos')
                ->color('info'),
            Stat::make('Promociones Expiradas', $expiredPromos)
                ->description('Finalizadas o inactivas')
                ->color('danger'),
        ];
    }
}
