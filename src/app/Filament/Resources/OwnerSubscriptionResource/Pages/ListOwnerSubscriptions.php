<?php

namespace App\Filament\Resources\OwnerSubscriptionResource\Pages;

use App\Filament\Resources\OwnerSubscriptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOwnerSubscriptions extends ListRecords
{
    protected static string $resource = OwnerSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
