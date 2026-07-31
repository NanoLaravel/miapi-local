<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromotionResource\Pages;
use App\Models\Promotion;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PromotionResource extends Resource
{
    protected static ?string $model = Promotion::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Promociones';
    protected static ?string $modelLabel = 'Promoción';
    protected static ?string $pluralModelLabel = 'Promociones';
    protected static ?string $navigationGroup = 'Monetización';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('owner_id')->label('Negocio')->relationship('owner', 'name')->searchable()->preload()->required(),
                TextInput::make('name')->label('Nombre')->required()->maxLength(255),
                TextInput::make('code')->label('Código')->required()->maxLength(50),
                Select::make('discount_type')->label('Tipo de descuento')->options([
                    'percentage' => 'Porcentaje',
                    'fixed' => 'Fijo',
                ])->default('percentage')->required(),
                TextInput::make('discount_value')->label('Valor')->numeric()->required()->default(0),
                DatePicker::make('starts_at')->label('Inicio'),
                DatePicker::make('ends_at')->label('Fin'),
                Toggle::make('is_active')->label('Activa')->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable()->sortable()->grow(),
                Tables\Columns\TextColumn::make('code')->label('Código')->searchable()->copyable()->copyMessage('Código copiado'),
                Tables\Columns\TextColumn::make('owner.name')->label('Negocio')->searchable()->sortable()->grow(),
                Tables\Columns\TextColumn::make('discount_type')->label('Tipo')->sortable()->badge(),
                Tables\Columns\TextColumn::make('discount_value')->label('Valor')->sortable()->formatStateUsing(function ($state, $record) {
                    if ($record->discount_type === 'percentage') {
                        return $state . '%';
                    }
                    return '$' . number_format($state, 2);
                }),
                Tables\Columns\TextColumn::make('starts_at')->label('Inicio')->date('d/m/Y')->sortable()->placeholder('Sin inicio'),
                Tables\Columns\TextColumn::make('ends_at')->label('Fin')->date('d/m/Y')->sortable()->placeholder('Sin fin'),
                Tables\Columns\TextColumn::make('is_active')->label('Activa')->sortable()->badge()->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('created_at')->label('Creado')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')->label('Estado')->options([
                    '1' => 'Activa',
                    '0' => 'Inactiva',
                ]),
                Tables\Filters\SelectFilter::make('discount_type')->label('Tipo de descuento')->options([
                    'percentage' => 'Porcentaje',
                    'fixed' => 'Fijo',
                ]),
                Tables\Filters\Filter::make('active_now')->label('Activas ahora')->query(fn ($q) => $q->where('is_active', true)->whereDate('starts_at', '<=', now())->whereDate('ends_at', '>=', now()))->toggle(),
                Tables\Filters\Filter::make('expired')->label('Expiradas')->query(fn ($q) => $q->where('is_active', false)->orWhereDate('ends_at', '<', now()))->toggle(),
                Tables\Filters\Filter::make('this_month')->label('Este mes')->query(fn ($q) => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year))->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('toggle_active')
                    ->label(fn ($record) => $record->is_active ? 'Desactivar' : 'Activar')
                    ->icon(fn ($record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn ($record) => $record->is_active ? 'danger' : 'success')
                    ->action(fn ($record) => $record->update(['is_active' => !$record->is_active])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate_bulk')
                        ->label('Activar seleccionadas')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->icon('heroicon-o-check-circle'),
                    Tables\Actions\BulkAction::make('deactivate_bulk')
                        ->label('Desactivar seleccionadas')
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
            'index' => Pages\ListPromotions::route('/'),
            'create' => Pages\CreatePromotion::route('/create'),
            'edit' => Pages\EditPromotion::route('/{record}/edit'),
        ];
    }
}
