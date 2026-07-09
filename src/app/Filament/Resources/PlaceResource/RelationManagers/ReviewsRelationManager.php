<?php

namespace App\Filament\Resources\PlaceResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Calificación General')
                    ->schema([
                        Forms\Components\TextInput::make('rating')
                            ->label('Calificación general (1-5)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(5)
                            ->step(1)
                            ->helperText('Calificación global del lugar. Obligatoria.')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('comment')
                            ->label('Comentario')
                            ->placeholder('Escribe aquí tu opinión sobre el lugar...')
                            ->rows(4)
                            ->maxLength(1000)
                            ->helperText('Opcional. Máximo 1000 caracteres.')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Calificación por Categorías (Opcional)')
                    ->schema([
                        Forms\Components\TextInput::make('cleanliness')
                            ->label('Limpieza')
                            ->numeric()
                            ->nullable()
                            ->minValue(1)
                            ->maxValue(5)
                            ->step(1),
                        Forms\Components\TextInput::make('accuracy')
                            ->label('Exactitud')
                            ->numeric()
                            ->nullable()
                            ->minValue(1)
                            ->maxValue(5)
                            ->step(1),
                        Forms\Components\TextInput::make('check_in')
                            ->label('Check-in')
                            ->numeric()
                            ->nullable()
                            ->minValue(1)
                            ->maxValue(5)
                            ->step(1),
                        Forms\Components\TextInput::make('communication')
                            ->label('Comunicación')
                            ->numeric()
                            ->nullable()
                            ->minValue(1)
                            ->maxValue(5)
                            ->step(1),
                        Forms\Components\TextInput::make('location')
                            ->label('Ubicación')
                            ->numeric()
                            ->nullable()
                            ->minValue(1)
                            ->maxValue(5)
                            ->step(1),
                        Forms\Components\TextInput::make('price')
                            ->label('Precio / Valor')
                            ->numeric()
                            ->nullable()
                            ->minValue(1)
                            ->maxValue(5)
                            ->step(1),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('rating')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rating')
                    ->label('⭐ Calificación')
                    ->numeric(1)
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => match(true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        default     => 'danger',
                    }),
                Tables\Columns\TextColumn::make('comment')
                    ->label('Comentario')
                    ->limit(60)
                    ->placeholder('Sin comentario')
                    ->wrap(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
