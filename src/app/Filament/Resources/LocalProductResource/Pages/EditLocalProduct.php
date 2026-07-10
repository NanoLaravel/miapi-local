<?php

namespace App\Filament\Resources\LocalProductResource\Pages;

use App\Filament\Resources\LocalProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLocalProduct extends EditRecord
{
    protected static string $resource = LocalProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
