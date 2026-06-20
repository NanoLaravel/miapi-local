<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers\ImagesRelationManager;
use App\Models\Event;
use App\Models\Place;
use App\Models\Image;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\ImageEntry;
use Illuminate\Support\Facades\Storage;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Eventos';
    protected static ?string $modelLabel = 'Evento';
    protected static ?string $pluralModelLabel = 'Eventos';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Evento')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('start_date')
                                    ->label('Fecha de inicio')
                                    ->required()
                                    ->native(false),
                                Forms\Components\DateTimePicker::make('end_date')
                                    ->label('Fecha de fin')
                                    ->required()
                                    ->native(false)
                                    ->after('start_date'),
                            ]),
                        Forms\Components\TextInput::make('location')
                            ->label('Ubicación')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('latitude')
                                    ->label('Latitud')
                                    ->numeric()
                                    ->step(0.0000001),
                                Forms\Components\TextInput::make('longitude')
                                    ->label('Longitud')
                                    ->numeric()
                                    ->step(0.0000001),
                            ]),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detalles Adicionales')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('price')
                                    ->label('Precio')
                                    ->numeric()
                                    ->prefix('$')
                                    ->step(0.01),
                                Forms\Components\TextInput::make('contact_phone')
                                    ->label('Teléfono de contacto')
                                    ->tel()
                                    ->maxLength(50),
                                Forms\Components\TextInput::make('contact_email')
                                    ->label('Email de contacto')
                                    ->email()
                                    ->maxLength(255),
                            ]),
                        Forms\Components\TextInput::make('website')
                            ->label('Sitio web')
                            ->url()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('place_id')
                            ->label('Lugar asociado')
                            ->options(Place::pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('Seleccionar lugar (opcional)')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Estado')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true)
                            ->helperText('Los eventos inactivos no se muestran en la app'),
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Destacado')
                            ->default(false)
                            ->helperText('Los eventos destacados aparecen primero'),
                    ])
                    ->columns(2),

                // Imagen principal (para compatibilidad con datos existentes)
                FileUpload::make('image_path')
                    ->label('Imagen principal')
                    ->image()
                    ->imagePreviewHeight('150')
                    ->directory('eventos')
                    ->disk('public')
                    ->previewable(true)
                    ->openable()
                    ->helperText('Imagen principal del evento (se muestra en listados).')
                    ->columnSpanFull(),

                // Imágenes iniciales solo para creación
                Forms\Components\Repeater::make('initial_images')
                    ->label('Imágenes del evento')
                    ->schema([
                        Forms\Components\FileUpload::make('path')
                            ->label('Imagen')
                            ->image()
                            ->directory('eventos')
                            ->disk('public')
                            ->required(),
                        Forms\Components\TextInput::make('description')
                            ->label('Descripción de la imagen')
                            ->maxLength(255),
                    ])
                    ->default([])
                    ->columns(2)
                    ->columnSpanFull()
                    ->helperText('Puedes agregar más imágenes luego desde la pestaña "Imágenes".')
                    ->dehydrated(false)
                    ->visible(fn(string $context) => $context === 'create'),

                // Galería de imágenes con Repeater para edición
                Repeater::make('images')
                    ->relationship('images')
                    ->label('Galería de Imágenes')
                    ->schema([
                        FileUpload::make('path')
                            ->label('Imagen')
                            ->image()
                            ->imagePreviewHeight('100')
                            ->directory('eventos')
                            ->disk('public')
                            ->previewable(true)
                            ->openable()
                            ->hiddenLabel(),
                    ])
                    ->columns(1)
                    ->grid(2)
                    ->minItems(0)
                    ->maxItems(8),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Imagen')
                    ->circular()
                    ->size(40)
                    ->disk('public'),
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Inicio')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Fin')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->label('Ubicación')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Destacado')
                    ->boolean(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(function (Event $record) {
                        if (!$record->is_active) return 'Inactivo';
                        if ($record->hasEnded()) return 'Finalizado';
                        if ($record->isOngoing()) return 'En curso';
                        return 'Próximo';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'En curso' => 'success',
                        'Próximo' => 'warning',
                        'Finalizado' => 'gray',
                        'Inactivo' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('start_date', 'desc')
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->label('Solo activos')
                    ->query(fn ($query) => $query->where('is_active', true)),
                Tables\Filters\Filter::make('featured')
                    ->label('Solo destacados')
                    ->query(fn ($query) => $query->where('is_featured', true)),
                Tables\Filters\Filter::make('upcoming')
                    ->label('Próximos')
                    ->query(fn ($query) => $query->where('start_date', '>', now())),
                Tables\Filters\Filter::make('ongoing')
                    ->label('En curso')
                    ->query(fn ($query) => $query->where('start_date', '<=', now())->where('end_date', '>=', now())),
                Tables\Filters\SelectFilter::make('place_id')
                    ->label('Lugar')
                    ->options(Place::pluck('name', 'id')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Detalles del Evento')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('title')->label('Título'),
                            TextEntry::make('start_date')->label('Inicio')->dateTime('d/m/Y H:i'),
                            TextEntry::make('end_date')->label('Fin')->dateTime('d/m/Y H:i'),
                        ]),
                        TextEntry::make('description')->label('Descripción')->columnSpanFull(),
                        TextEntry::make('location')->label('Ubicación'),
                        Grid::make(2)->schema([
                            TextEntry::make('latitude')->label('Latitud'),
                            TextEntry::make('longitude')->label('Longitud'),
                        ]),
                    ])
                    ->columns(2),

                Section::make('Galería de Imágenes')
                    ->schema([
                        Grid::make(3)->schema(function (Event $record) {
                            return $record->images->map(function ($image) {
                                return ImageEntry::make('path')
                                    ->hiddenLabel()
                                    ->width('100px')
                                    ->height('100px')
                                    ->image()
                                    ->getStateUsing(fn() => Storage::url($image->path));
                            })->toArray();
                        }),
                    ])
                    ->collapsible()
                    ->columnSpan('full'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ImagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
