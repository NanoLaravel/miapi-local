<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdvertisementResource\Pages;
use App\Filament\Resources\AdvertisementResource\RelationManagers\ImagesRelationManager;
use App\Models\Advertisement;
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

class AdvertisementResource extends Resource
{
    protected static ?string $model = Advertisement::class;
    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationLabel = 'Publicidad';
    protected static ?string $modelLabel = 'Anuncio';
    protected static ?string $pluralModelLabel = 'Anuncios';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Anuncio')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(2)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('link_url')
                            ->label('URL de destino')
                            ->url()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->helperText('URL a la que se redirige al hacer clic'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Configuración de Visualización')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->label('Tipo de anuncio')
                                    ->options(Advertisement::getTypes())
                                    ->required()
                                    ->default('banner'),
                                Forms\Components\Select::make('position')
                                    ->label('Posición')
                                    ->options(Advertisement::getPositions())
                                    ->required()
                                    ->default('all'),
                            ]),
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
                        Forms\Components\TextInput::make('priority')
                            ->label('Prioridad')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->helperText('Mayor prioridad = aparece primero (0-100)'),
                        Forms\Components\Select::make('place_id')
                            ->label('Lugar asociado (opcional)')
                            ->options(Place::pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('Ninguno')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Estado')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true)
                            ->helperText('Los anuncios inactivos no se muestran en la app'),
                    ]),

                // Imagen principal (para compatibilidad con datos existentes)
                FileUpload::make('image_path')
                    ->label('Imagen principal')
                    ->image()
                    ->imagePreviewHeight('150')
                    ->directory('publicidad')
                    ->disk('public')
                    ->previewable(true)
                    ->openable()
                    ->helperText('Imagen principal del anuncio. Tamaño recomendado: 1200x400px para banners.')
                    ->columnSpanFull(),

                // Imágenes iniciales solo para creación
                Forms\Components\Repeater::make('initial_images')
                    ->label('Imágenes adicionales')
                    ->schema([
                        Forms\Components\FileUpload::make('path')
                            ->label('Imagen')
                            ->image()
                            ->directory('publicidad')
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
                            ->directory('publicidad')
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
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Advertisement::getTypes()[$state] ?? $state),
                Tables\Columns\TextColumn::make('position')
                    ->label('Posición')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Advertisement::getPositions()[$state] ?? $state),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Inicio')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Fin')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioridad')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(function (Advertisement $record) {
                        if (!$record->is_active) return 'Inactivo';
                        if (!$record->isValid()) return 'Expirado';
                        return 'Vigente';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Vigente' => 'success',
                        'Expirado' => 'warning',
                        'Inactivo' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('priority', 'desc')
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->label('Solo activos')
                    ->query(fn ($query) => $query->where('is_active', true)),
                Tables\Filters\Filter::make('valid')
                    ->label('Vigentes')
                    ->query(fn ($query) => $query->where('is_active', true)
                        ->where('start_date', '<=', now())
                        ->where('end_date', '>=', now())),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(Advertisement::getTypes()),
                Tables\Filters\SelectFilter::make('position')
                    ->label('Posición')
                    ->options(Advertisement::getPositions()),
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
                Section::make('Detalles del Anuncio')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('title')->label('Título'),
                            TextEntry::make('link_url')->label('URL de destino'),
                        ]),
                        TextEntry::make('description')->label('Descripción')->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Galería de Imágenes')
                    ->schema([
                        Grid::make(3)->schema(function (Advertisement $record) {
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
            'index' => Pages\ListAdvertisements::route('/'),
            'create' => Pages\CreateAdvertisement::route('/create'),
            'edit' => Pages\EditAdvertisement::route('/{record}/edit'),
        ];
    }
}
