<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Promotion;
use Illuminate\Support\Carbon;

class PromotionUsageChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Uso de Promociones (Últimos 6 meses)';

    public function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $months = collect(range(0, 5))->map(function ($i) {
            return Carbon::now()->subMonths($i)->format('Y-m');
        })->reverse()->values();

        $promoCounts = $months->map(function ($month) {
            return Promotion::whereYear('created_at', substr($month, 0, 4))
                ->whereMonth('created_at', substr($month, 5, 2))
                ->count();
        });

        $activePromos = $months->map(function ($month) {
            return Promotion::whereYear('created_at', substr($month, 0, 4))
                ->whereMonth('created_at', substr($month, 5, 2))
                ->where('is_active', true)
                ->count();
        });

        return [
            'datasets' => [
                [
                    'label' => 'Promociones Creadas',
                    'data' => $promoCounts,
                    'backgroundColor' => '#f59e42',
                ],
                [
                    'label' => 'Promociones Activas',
                    'data' => $activePromos,
                    'backgroundColor' => '#10b981',
                ],
            ],
            'labels' => $months->toArray(),
        ];
    }
}
