<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlaceResource\Pages;
use App\Filament\Resources\PlaceResource\RelationManagers;
use App\Filament\Resources\PlaceResource\RelationManagers\ImagesRelationManager;
use App\Filament\Resources\PlaceResource\RelationManagers\ReviewsRelationManager;
use App\Filament\Resources\PlaceResource\RelationManagers\PricesRelationManager;
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
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;

class PlaceResource extends Resource
{
    protected static ?string $model = Place::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Lugares'; // <-- aquí

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')->label('Tipo')->sortable(),
                Tables\Columns\TextColumn::make('address')->label('Dirección')->searchable(),
                Tables\Columns\TextColumn::make('latitude')->label('Latitud')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('longitude')->label('Longitud')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('phone')->label('Teléfono')->searchable(),
                Tables\Columns\TextColumn::make('website')->label('Sitio web')->searchable(),
                Tables\Columns\TextColumn::make('rating')->label('Calificación')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Creado')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')->label('Actualizado')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Puedes agregar filtros aquí si lo necesitas
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
    // ...existing code...

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre del lugar')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Descripción del lugar')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('address')
                    ->label('Dirección')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('latitude')
                    ->label('Latitud')
                    ->numeric(),
                Forms\Components\TextInput::make('longitude')
                    ->label('Longitud')
                    ->numeric(),
                Forms\Components\TextInput::make('phone')
                    ->label('Teléfono')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\TextInput::make('website')
                    ->label('Sitio web')
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->label('Tipo de lugar')
                    ->options([
                        'restaurant' => 'Restaurante',
                        'hotel' => 'Hotel',
                        'recreation' => 'Recreación',
                        'other' => 'Otro',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('rating')
                    ->label('Calificación')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('facilities')
                    ->label('Instalaciones'),
                Select::make('categories')
                    ->label('Categorías')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Repeater::make('initial_images')
                    ->label('Imágenes del lugar')
                    ->schema([
                        Forms\Components\FileUpload::make('path')
                            ->label('Imagen')
                            ->image()
                            ->directory('images')
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
                            ->directory('lugares')
                            ->disk('public')
                            ->previewable(true)
                            ->openable()  // Permite abrir la imagen en una nueva pestaña                              
                            ->hiddenLabel(),                            
                            
                    ])
                    ->columns(1)
                    ->grid(2)  // Muestra los items en grid de 2 columnas
                    ->minItems(0)
                    ->maxItems(8),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Detalles del lugar')
                    ->schema([
                        Grid::make(2)->schema([
                            \Filament\Infolists\Components\TextEntry::make('name')->label('Nombre'),
                            \Filament\Infolists\Components\TextEntry::make('type')->label('Tipo'),
                        ]),
                    ]),

                Section::make('Galería de Imágenes')
                    ->schema([
                        Grid::make(3)->schema(function (Place $record) {
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
            ReviewsRelationManager::class,
            PricesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlaces::route('/'),
            'create' => Pages\CreatePlace::route('/create'),            
            'edit' => Pages\EditPlace::route('/{record}/edit'),
        ];
    }
}
