<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionPlanResource\Pages;
use App\Models\SubscriptionPlan;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionPlanResource extends Resource
{
    protected static ?string $model = SubscriptionPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Planes';
    protected static ?string $modelLabel = 'Plan';
    protected static ?string $pluralModelLabel = 'Planes';
    protected static ?string $navigationGroup = 'Monetización';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Datos del plan')
                    ->schema([
                        TextInput::make('name')->label('Nombre')->required()->maxLength(255),
                        TextInput::make('slug')->label('Slug')->required()->maxLength(255)->unique(ignoreRecord: true),
                        Textarea::make('description')->label('Descripción')->rows(3)->columnSpanFull(),
                        Select::make('target_type')->label('Tipo de objetivo')->options([
                            'local_product' => 'Producto local',
                            'place' => 'Lugar',
                            'hybrid' => 'Híbrido',
                        ])->required(),
                    ])->columns(2),

                Section::make('Precios y límites')
                    ->schema([
                        TextInput::make('price_monthly')->label('Precio mensual')->numeric()->prefix('$'),
                        TextInput::make('price_yearly')->label('Precio anual')->numeric()->prefix('$'),
                        TextInput::make('lead_limit')->label('Límite de leads')->numeric()->default(0),
                        TextInput::make('featured_limit')->label('Límite de destacados')->numeric()->default(0),
                    ])->columns(2),

                Section::make('Habilitadores')
                    ->schema([
                        Toggle::make('analytics_enabled')->label('Analytics habilitado')->default(false),
                        Toggle::make('promotions_enabled')->label('Promociones habilitadas')->default(false),
                        Toggle::make('is_active')->label('Activo')->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable()->sortable()->grow(),
                Tables\Columns\TextColumn::make('slug')->label('Slug')->searchable()->limit(20),
                Tables\Columns\TextColumn::make('target_type')->label('Tipo')->sortable()->formatStateUsing(fn (string $state): string => match ($state) {
                    'local_product' => 'Producto local',
                    'place' => 'Lugar',
                    'hybrid' => 'Híbrido',
                    default => $state,
                }),
                Tables\Columns\TextColumn::make('price_monthly')->label('Mensual')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('price_yearly')->label('Anual')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('lead_limit')->label('Leads')->sortable(),
                Tables\Columns\TextColumn::make('featured_limit')->label('Destacados')->sortable(),
                Tables\Columns\IconColumn::make('analytics_enabled')->label('Analytics')->boolean()->sortable(),
                Tables\Columns\IconColumn::make('promotions_enabled')->label('Promos')->boolean()->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('Activo')->boolean()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')->label('Estado')->options([
                    '1' => 'Activo',
                    '0' => 'Inactivo',
                ]),
                Tables\Filters\SelectFilter::make('target_type')->label('Tipo')->options([
                    'local_product' => 'Producto local',
                    'place' => 'Lugar',
                    'hybrid' => 'Híbrido',
                ]),
                Tables\Filters\Filter::make('with_analytics')->label('Con analytics')->query(fn ($q) => $q->where('analytics_enabled', true)),
                Tables\Filters\Filter::make('with_promotions')->label('Con promociones')->query(fn ($q) => $q->where('promotions_enabled', true)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activar seleccionados')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->icon('heroicon-o-check-circle'),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Desactivar seleccionados')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->icon('heroicon-o-x-circle'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptionPlans::route('/'),
            'create' => Pages\CreateSubscriptionPlan::route('/create'),
            'edit' => Pages\EditSubscriptionPlan::route('/{record}/edit'),
        ];
    }
}
