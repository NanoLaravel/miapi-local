<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LocalProductResource\Pages;
use App\Models\LocalProduct;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PlaceResource\RelationManagers\ImagesRelationManager;

class LocalProductResource extends Resource
{
    protected static ?string $model = LocalProduct::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Productos Locales';
    protected static ?string $modelLabel = 'Producto Local';
    protected static ?string $pluralModelLabel = 'Productos Locales';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detalles del Producto')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre del producto')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('price')
                            ->label('Precio (Opcional)')
                            ->numeric()
                            ->prefix('$')
                            ->helperText('Deja vacío si prefieres no mostrar el precio.'),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true)
                            ->helperText('Los productos inactivos no aparecerán en la app.'),
                        Toggle::make('is_featured')
                            ->label('Destacado')
                            ->default(false)
                            ->helperText('Los productos destacados aparecen en las secciones principales.'),
                    ])
                    ->columns(2),

                Section::make('Información del Productor / Emprendimiento')
                    ->schema([
                        TextInput::make('producer_name')
                            ->label('Nombre del productor o emprendimiento')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Teléfono / WhatsApp')
                            ->tel()
                            ->required()
                            ->maxLength(50)
                            ->helperText('Ej: 3123456789 (se generará automáticamente enlace de WhatsApp)'),
                        TextInput::make('approximate_location')
                            ->label('Ubicación aproximada')
                            ->maxLength(255)
                            ->helperText('Ej: Cúcuta, Norte de Santander'),
                        TextInput::make('facebook_url')
                            ->label('Facebook (Enlace)')
                            ->nullable()
                            ->url()
                            ->maxLength(255)
                            ->helperText('Opcional.'),
                        TextInput::make('instagram_url')
                            ->label('Instagram (Enlace)')
                            ->nullable()
                            ->url()
                            ->maxLength(255)
                            ->helperText('Opcional.'),
                    ])
                    ->columns(2),

                Section::make('Galería de Imágenes')
                    ->schema([
                        // Imágenes iniciales solo para creación (opcionales)
                        Repeater::make('initial_images')
                            ->label('Imágenes del producto (opcional)')
                            ->schema([
                                FileUpload::make('path')
                                    ->label('Imagen')
                                    ->image()
                                    ->directory('productos')
                                    ->disk('public'),
                                TextInput::make('description')
                                    ->label('Descripción de la imagen')
                                    ->maxLength(255),
                            ])
                            ->default([])
                            ->minItems(0)
                            ->columns(2)
                            ->columnSpanFull()
                            ->helperText('Opcional. Puedes agregar más imágenes luego.')
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
                                    ->directory('productos')
                                    ->disk('public')
                                    ->previewable(true)
                                    ->openable()
                                    ->hiddenLabel()
                                    ->required(),
                            ])
                            ->columns(1)
                            ->grid(2)
                            ->minItems(0)
                            ->maxItems(8)
                            ->visible(fn(string $context) => $context === 'edit'),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('images.path')
                    ->label('Imagen')
                    ->circular()
                    ->size(40)
                    ->disk('public')
                    ->limit(1),
                Tables\Columns\TextColumn::make('name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('producer_name')
                    ->label('Productor / Emprendimiento')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money('COP')
                    ->placeholder('No especificado')
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Destacado')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('is_active')
                    ->label('Solo activos')
                    ->query(fn ($query) => $query->where('is_active', true)),
                Tables\Filters\Filter::make('is_featured')
                    ->label('Solo destacados')
                    ->query(fn ($query) => $query->where('is_featured', true)),
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

    public static function getRelations(): array
    {
        return [
            // Las imágenes secundarias se manejan directamente mediante el morph relationship repeater en el form
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLocalProducts::route('/'),
            'create' => Pages\CreateLocalProduct::route('/create'),
            'edit' => Pages\EditLocalProduct::route('/{record}/edit'),
        ];
    }
}
