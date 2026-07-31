<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReservationResource\Pages;
use App\Models\Reservation;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Reservas';
    protected static ?string $modelLabel = 'Reserva';
    protected static ?string $pluralModelLabel = 'Reservas';
    protected static ?string $navigationGroup = 'Monetización';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('lead_id')->label('Lead ID')->required()->numeric(),
                Select::make('owner_id')->label('Negocio')->relationship('owner', 'name')->searchable()->preload()->required(),
                Select::make('user_id')->label('Usuario')->relationship('user', 'name')->searchable()->preload()->required(),
                TextInput::make('place_id')->label('Lugar ID')->nullable()->numeric(),
                TextInput::make('reservation_code')->label('Código')->required()->maxLength(255),
                DatePicker::make('check_in_date')->label('Llegada'),
                DatePicker::make('check_out_date')->label('Salida'),
                TextInput::make('nights')->label('Noches')->numeric()->default(1),
                TextInput::make('total_amount')->label('Monto total')->numeric()->default(0),
                Select::make('status')->label('Estado')->options([
                    'pending' => 'Pendiente',
                    'confirmed' => 'Confirmada',
                    'paid' => 'Pagada',
                    'cancelled' => 'Cancelada',
                    'rejected' => 'Rechazada',
                ])->default('pending'),
                Select::make('payment_method')->label('Método de pago')->options([
                    'cash' => 'Efectivo',
                    'card' => 'Tarjeta',
                    'transfer' => 'Transferencia',
                    'other' => 'Otro',
                ])->nullable(),
                Textarea::make('notes')->label('Notas')->rows(3)->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reservation_code')->label('Código')->searchable()->sortable()->grow(),
                Tables\Columns\TextColumn::make('owner.name')->label('Negocio')->searchable()->sortable()->grow(),
                Tables\Columns\TextColumn::make('user.name')->label('Usuario')->searchable()->sortable()->grow(),
                Tables\Columns\TextColumn::make('status')->label('Estado')->sortable()->badge()->color(fn (string $state): string => match ($state) {
                    'pending' => 'warning',
                    'confirmed' => 'success',
                    'paid' => 'success',
                    'cancelled' => 'danger',
                    'rejected' => 'danger',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('total_amount')->label('Monto')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('payment_method')->label('Pago')->sortable()->badge(),
                Tables\Columns\TextColumn::make('check_in_date')->label('Llegada')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Creado')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Estado')->options([
                    'pending' => 'Pendiente',
                    'confirmed' => 'Confirmada',
                    'paid' => 'Pagada',
                    'cancelled' => 'Cancelada',
                    'rejected' => 'Rechazada',
                ]),
                Tables\Filters\SelectFilter::make('payment_method')->label('Método de pago')->options([
                    'cash' => 'Efectivo',
                    'card' => 'Tarjeta',
                    'transfer' => 'Transferencia',
                    'other' => 'Otro',
                ]),
                Tables\Filters\Filter::make('confirmed')->label('Confirmadas/Pagadas')->query(fn ($q) => $q->whereIn('status', ['confirmed', 'paid']))->toggle(),
                Tables\Filters\Filter::make('cancelled')->label('Canceladas/Rechazadas')->query(fn ($q) => $q->whereIn('status', ['cancelled', 'rejected']))->toggle(),
                Tables\Filters\Filter::make('this_month')->label('Este mes')->query(fn ($q) => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year))->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('confirm')
                    ->label('Confirmar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn ($record) => $record->update(['status' => 'confirmed']))
                    ->hidden(fn ($record) => in_array($record->status, ['confirmed', 'paid', 'cancelled', 'rejected'])),
                Tables\Actions\Action::make('cancel')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(fn ($record) => $record->update(['status' => 'cancelled']))
                    ->requiresConfirmation()
                    ->hidden(fn ($record) => in_array($record->status, ['cancelled', 'rejected'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('confirm_bulk')
                        ->label('Confirmar seleccionadas')
                        ->action(fn ($records) => $records->each->update(['status' => 'confirmed']))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->icon('heroicon-o-check-circle'),
                    Tables\Actions\BulkAction::make('cancel_bulk')
                        ->label('Cancelar seleccionadas')
                        ->action(fn ($records) => $records->each->update(['status' => 'cancelled']))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->icon('heroicon-o-x-circle'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReservations::route('/'),
            'create' => Pages\CreateReservation::route('/create'),
            'edit' => Pages\EditReservation::route('/{record}/edit'),
        ];
    }
}
