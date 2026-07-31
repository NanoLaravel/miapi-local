<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Lead;

class LeadStatusChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Distribución de Leads por Estado';

    public function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $statuses = ['pending', 'contacted', 'interested', 'converted', 'lost'];
        $colors = ['#f59e42', '#3b82f6', '#10b981', '#6366f1', '#ef4444'];
        $counts = [];

        foreach ($statuses as $status) {
            $counts[] = Lead::where('status', $status)->count();
        }

        return [
            'datasets' => [
                [
                    'data' => $counts,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => array_map(fn($s) => ucfirst($s), $statuses),
        ];
    }
}
