<?php

namespace App\Filament\Resources\PlaceResource\Pages;

use App\Filament\Resources\PlaceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreatePlace extends CreateRecord
{
    protected static string $resource = PlaceResource::class;

    protected function afterCreate(): void
{
    $images = $this->form->getRawState()['initial_images'] ?? [];

    foreach ($images as $imageData) {
        $rawPath = $imageData['path'] ?? null;

        // Asegura que si es un array (por ejemplo: ['uuid' => 'ruta']), se tome solo el valor
        if (is_array($rawPath)) {
            $path = array_values($rawPath)[0]; // Extrae la primera ruta válida
        } else {
            $path = $rawPath;
        }

        $this->record->images()->create([
            'path' => $path,
            'description' => $imageData['description'] ?? null,
        ]);
    }

    \Log::info('afterCreate: imágenes procesadas correctamente.');
}

      
  
}

