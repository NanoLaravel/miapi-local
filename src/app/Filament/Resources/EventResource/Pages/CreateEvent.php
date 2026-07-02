<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

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

        \Log::info('afterCreate (Event): imágenes procesadas correctamente.');
    }
}
