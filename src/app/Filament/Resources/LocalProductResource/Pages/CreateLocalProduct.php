<?php

namespace App\Filament\Resources\LocalProductResource\Pages;

use App\Filament\Resources\LocalProductResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Filament\Resources\Pages\CreateRecord;

class CreateLocalProduct extends CreateRecord
{
    protected static string $resource = LocalProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

        return $data;
    }

    protected function afterCreate(): void
    {
        $images = $this->form->getRawState()['initial_images'] ?? [];

        foreach ($images as $imageData) {
            $rawPath = $imageData['path'] ?? null;

            if (is_array($rawPath)) {
                $path = array_values($rawPath)[0];
            } else {
                $path = $rawPath;
            }

            if ($path) {
                $this->record->images()->create([
                    'path' => $path,
                    'description' => $imageData['description'] ?? null,
                ]);
            }
        }

        Log::info('afterCreate (LocalProduct): imágenes procesadas correctamente.');
    }
}
