<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Reservation;
use App\Models\OwnerSubscription;
use Illuminate\Support\Carbon;

class RevenueChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Ingresos Mensuales (Reservas + Suscripciones)';

    public function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $months = collect(range(0, 5))->map(function ($i) {
            return Carbon::now()->subMonths($i)->format('Y-m');
        })->reverse()->values();

        $reservationRevenue = $months->map(function ($month) {
            $year = substr($month, 0, 4);
            $monthNum = substr($month, 5, 2);
            return Reservation::whereYear('created_at', $year)
                ->whereMonth('created_at', $monthNum)
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount');
        });

        $subscriptionRevenue = $months->map(function ($month) {
            $year = substr($month, 0, 4);
            $monthNum = substr($month, 5, 2);
            return OwnerSubscription::whereYear('started_at', $year)
                ->whereMonth('started_at', $monthNum)
                ->where('payment_status', 'paid')
                ->join('subscription_plans', 'owner_subscriptions.subscription_plan_id', '=', 'subscription_plans.id')
                ->selectRaw('SUM(subscription_plans.price_monthly) as revenue')
                ->value('revenue') ?? 0;
        });

        return [
            'datasets' => [
                [
                    'label' => 'Reservas',
                    'data' => $reservationRevenue,
                    'borderColor' => '#f59e42',
                    'backgroundColor' => 'rgba(245, 158, 66, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Suscripciones',
                    'data' => $subscriptionRevenue,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $months->toArray(),
        ];
    }
}
