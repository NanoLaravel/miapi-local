<?php

namespace App\Filament\Resources\AdvertisementResource\Pages;

use App\Filament\Resources\AdvertisementResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdvertisement extends CreateRecord
{
    protected static string $resource = AdvertisementResource::class;

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

        \Log::info('afterCreate (Advertisement): imágenes procesadas correctamente.');
    }
}
