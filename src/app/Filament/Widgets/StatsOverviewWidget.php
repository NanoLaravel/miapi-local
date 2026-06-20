<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Place;
use App\Models\User;
use App\Models\Review;
use App\Models\Category;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Lugares', Place::count()),
            Stat::make('Usuarios', User::count()),
            Stat::make('Reseñas', Review::count()),
            Stat::make('Categorías', Category::count()),
        ];
    }
}
