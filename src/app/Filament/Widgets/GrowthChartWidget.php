<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\User;
use App\Models\Place;
use App\Models\Review;
use Illuminate\Support\Carbon;

class GrowthChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Crecimiento de Usuarios, Lugares y Reseñas';

    public function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $months = collect(range(0, 5))->map(function ($i) {
            return Carbon::now()->subMonths($i)->format('Y-m');
        })->reverse()->values();

        $userCounts = $months->map(fn($month) => User::whereYear('created_at', substr($month, 0, 4))
            ->whereMonth('created_at', substr($month, 5, 2))->count());
        $placeCounts = $months->map(fn($month) => Place::whereYear('created_at', substr($month, 0, 4))
            ->whereMonth('created_at', substr($month, 5, 2))->count());
        $reviewCounts = $months->map(fn($month) => Review::whereYear('created_at', substr($month, 0, 4))
            ->whereMonth('created_at', substr($month, 5, 2))->count());

        return [
            'datasets' => [
                [
                    'label' => 'Usuarios',
                    'data' => $userCounts,
                    'borderColor' => '#f59e42',
                ],
                [
                    'label' => 'Lugares',
                    'data' => $placeCounts,
                    'borderColor' => '#3b82f6',
                ],
                [
                    'label' => 'Reseñas',
                    'data' => $reviewCounts,
                    'borderColor' => '#10b981',
                ],
            ],
            'labels' => $months->toArray(),
        ];
    }
}
