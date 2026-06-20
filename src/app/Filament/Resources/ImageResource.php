<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImageResource\Pages;
use App\Filament\Resources\ImageResource\RelationManagers;
use App\Models\Image;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ImageResource extends Resource
{
    protected static ?string $model = Image::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Imágenes'; // <-- aquí

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('place_id')
                    ->label('Lugar')
                    ->relationship('place', 'name')
                    ->required(),
                Forms\Components\TextInput::make('path')
                    ->label('Ruta de la imagen')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ej: images/parque.jpg')
                    ->helperText('Ruta relativa o URL de la imagen.')
                    ->default('images/default.jpg'),
                Forms\Components\TextInput::make('description')
                    ->label('Descripción de la imagen')
                    ->maxLength(255)
                    ->placeholder('Ej: Vista principal del parque')
                    ->helperText('Breve descripción de la imagen.')
                    ->default('Sin descripción'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('place.name')
                    ->label('Lugar')
                    ->searchable()                    
                    ->sortable(),
                Tables\Columns\TextColumn::make('path')
                    ->label('Ruta de la imagen')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción de la imagen') 
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImages::route('/'),
            'create' => Pages\CreateImage::route('/create'),
            'edit' => Pages\EditImage::route('/{record}/edit'),
        ];
    }
}
