<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Reservation;
use App\Models\Lead;

class ReservationMetricsWidget extends BaseWidget
{
    protected ?string $heading = 'Métricas de Reservas';

    protected function getStats(): array
    {
        $totalRevenue = Reservation::where('status', '!=', 'cancelled')->sum('total_amount');
        $totalReservations = Reservation::count();
        $cancelled = Reservation::where('status', 'cancelled')->count();
        $avgValue = $totalReservations > 0 ? $totalRevenue / $totalReservations : 0;
        $conversionRate = $totalReservations > 0 ? ((Reservation::where('status', 'confirmed')->count() + Reservation::where('status', 'completed')->count()) / $totalReservations) * 100 : 0;
        $leadConversion = Lead::count() > 0 ? (Reservation::count() / Lead::count()) * 100 : 0;

        return [
            Stat::make('Ingreso Total Reservas', '$' . number_format($totalRevenue, 2))
                ->description('Total acumulado')
                ->color('success'),
            Stat::make('Valor Promedio Reserva', '$' . number_format($avgValue, 2))
                ->description('Promedio por reserva')
                ->color('info'),
            Stat::make('Tasa de Confirmación', number_format($conversionRate, 1) . '%')
                ->description('Reservas confirmadas/completadas')
                ->color($conversionRate > 50 ? 'success' : 'warning'),
            Stat::make('Conversión Lead → Reserva', number_format($leadConversion, 1) . '%')
                ->description('De leads generados')
                ->color($leadConversion > 20 ? 'success' : 'warning'),
        ];
    }
}
