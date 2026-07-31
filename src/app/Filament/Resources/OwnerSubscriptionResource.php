<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OwnerSubscriptionResource\Pages;
use App\Models\OwnerSubscription;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OwnerSubscriptionResource extends Resource
{
    protected static ?string $model = OwnerSubscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Suscripciones';
    protected static ?string $modelLabel = 'Suscripción';
    protected static ?string $pluralModelLabel = 'Suscripciones';
    protected static ?string $navigationGroup = 'Monetización';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('owner_id')->label('Propietario')->relationship('owner', 'name')->searchable()->preload()->required(),
                Select::make('subscription_plan_id')->label('Plan')->relationship('plan', 'name')->searchable()->preload()->required(),
                Select::make('status')->label('Estado')->options([
                    'active' => 'Activa',
                    'paused' => 'Pausada',
                    'cancelled' => 'Cancelada',
                    'expired' => 'Expirada',
                ])->required()->default('active'),
                Select::make('payment_status')->label('Estado de pago')->options([
                    'pending' => 'Pendiente',
                    'paid' => 'Pagado',
                    'failed' => 'Fallido',
                    'refunded' => 'Reintegrado',
                ])->required()->default('pending'),
                DateTimePicker::make('started_at')->label('Inicio')->required(),
                DateTimePicker::make('ends_at')->label('Fin')->nullable(),
                Toggle::make('auto_renew')->label('Renovación automática')->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('owner.name')->label('Propietario')->searchable()->sortable()->grow(),
                Tables\Columns\TextColumn::make('plan.name')->label('Plan')->searchable()->sortable()->badge(),
                Tables\Columns\TextColumn::make('status')->label('Estado')->sortable()->badge()->color(fn (string $state): string => match ($state) {
                    'active' => 'success',
                    'paused' => 'warning',
                    'cancelled' => 'danger',
                    'expired' => 'gray',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('payment_status')->label('Pago')->sortable()->badge()->color(fn (string $state): string => match ($state) {
                    'paid' => 'success',
                    'pending' => 'warning',
                    'failed' => 'danger',
                    'refunded' => 'info',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('started_at')->label('Inicio')->dateTime('d/m/Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('ends_at')->label('Fin')->dateTime('d/m/Y H:i')->sortable()->placeholder('Sin fecha'),
                Tables\Columns\IconColumn::make('auto_renew')->label('Auto-renovación')->boolean()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Estado')->options([
                    'active' => 'Activa',
                    'paused' => 'Pausada',
                    'cancelled' => 'Cancelada',
                    'expired' => 'Expirada',
                ]),
                Tables\Filters\SelectFilter::make('payment_status')->label('Pago')->options([
                    'pending' => 'Pendiente',
                    'paid' => 'Pagado',
                    'failed' => 'Fallido',
                    'refunded' => 'Reintegrado',
                ]),
                Tables\Filters\Filter::make('expiring_soon')->label('Vencen pronto')
                    ->query(fn ($q) => $q->whereBetween('ends_at', [now(), now()->addDays(30)]))
                    ->toggle(),
                Tables\Filters\Filter::make('recent')->label('Creadas este mes')
                    ->query(fn ($q) => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year))
                    ->toggle(),
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
                        ->label('Activar seleccionadas')
                        ->action(fn ($records) => $records->each->update(['status' => 'active']))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->icon('heroicon-o-check-circle'),
                    Tables\Actions\BulkAction::make('cancel')
                        ->label('Cancelar seleccionadas')
                        ->action(fn ($records) => $records->each->update(['status' => 'cancelled']))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->icon('heroicon-o-x-circle'),
                    Tables\Actions\BulkAction::make('mark_paid')
                        ->label('Marcar como pagadas')
                        ->action(fn ($records) => $records->each->update(['payment_status' => 'paid']))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->icon('heroicon-o-currency-dollar'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOwnerSubscriptions::route('/'),
            'create' => Pages\CreateOwnerSubscription::route('/create'),
            'edit' => Pages\EditOwnerSubscription::route('/{record}/edit'),
        ];
    }
}
