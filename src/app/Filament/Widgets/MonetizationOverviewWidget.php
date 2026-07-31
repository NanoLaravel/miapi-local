<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Models\OwnerSubscription;
use App\Models\Promotion;
use App\Models\Reservation;
use App\Models\SubscriptionPlan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MonetizationOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $activePlans = SubscriptionPlan::query()->where('is_active', true)->count();
        $activeSubs = OwnerSubscription::query()->where('status', 'active')->count();
        $leads = Lead::count();
        $reservations = Reservation::count();
        $promotions = Promotion::count();

        $thisMonthReservations = Reservation::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $lastMonthReservations = Reservation::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        $reservationTrend = $lastMonthReservations > 0 ? (($thisMonthReservations - $lastMonthReservations) / $lastMonthReservations) * 100 : 0;

        $thisMonthLeads = Lead::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $lastMonthLeads = Lead::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        $leadTrend = $lastMonthLeads > 0 ? (($thisMonthLeads - $lastMonthLeads) / $lastMonthLeads) * 100 : 0;

        $confirmedReservations = Reservation::whereIn('status', ['confirmed', 'completed'])->count();
        $conversionTrend = $reservations > 0 ? ($confirmedReservations / $reservations) * 100 : 0;

        return [
            Stat::make('Planes activos', $activePlans)
                ->description('Planes disponibles')
                ->color('success'),
            Stat::make('Suscripciones activas', $activeSubs)
                ->description('Usuarios con plan activo')
                ->color('info'),
            Stat::make('Leads', $leads)
                ->description(number_format($leadTrend, 0) . '% vs mes anterior')
                ->color($leadTrend >= 0 ? 'success' : 'danger'),
            Stat::make('Reservas', $reservations)
                ->description(number_format($reservationTrend, 0) . '% vs mes anterior')
                ->color($reservationTrend >= 0 ? 'success' : 'danger'),
            Stat::make('Promociones', $promotions)
                ->description(number_format($conversionTrend, 0) . '% confirmadas')
                ->color('warning'),
        ];
    }
}
