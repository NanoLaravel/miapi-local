<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadResource\Pages;
use App\Models\Lead;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Leads';
    protected static ?string $modelLabel = 'Lead';
    protected static ?string $pluralModelLabel = 'Leads';
    protected static ?string $navigationGroup = 'Monetización';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')->label('Usuario')->relationship('user', 'name')->searchable()->preload()->required(),
                Select::make('owner_id')->label('Negocio')->relationship('owner', 'name')->searchable()->preload()->required(),
                Select::make('leadable_type')->label('Tipo')->options([
                    'local_product' => 'Producto local',
                    'place' => 'Lugar',
                ])->required(),
                TextInput::make('leadable_id')->label('ID del recurso')->required()->numeric(),
                Select::make('contact_type')->label('Canal')->options([
                    'whatsapp' => 'WhatsApp',
                    'phone' => 'Teléfono',
                    'email' => 'Correo',
                ])->default('whatsapp'),
                Select::make('status')->label('Estado')->options([
                    'pending' => 'Pendiente',
                    'contacted' => 'Contactado',
                    'interested' => 'Interesado',
                    'rejected' => 'Rechazado',
                    'converted' => 'Convertido',
                ])->default('pending'),
                Select::make('source')->label('Fuente')->options([
                    'app' => 'App',
                    'web' => 'Web',
                    'manual' => 'Manual',
                ])->default('app'),
                Select::make('priority')->label('Prioridad')->options([
                    'low' => 'Baja',
                    'medium' => 'Media',
                    'high' => 'Alta',
                ])->default('medium'),
                Textarea::make('message')->label('Mensaje')->rows(4)->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('owner.name')->label('Negocio')->searchable()->sortable()->grow(),
                Tables\Columns\TextColumn::make('user.name')->label('Usuario')->searchable()->sortable()->grow(),
                Tables\Columns\TextColumn::make('leadable_type')->label('Tipo')->sortable()->badge()->color(fn (string $state): string => match ($state) {
                    'local_product' => 'info',
                    'place' => 'warning',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('status')->label('Estado')->sortable()->badge()->color(fn (string $state): string => match ($state) {
                    'pending' => 'warning',
                    'contacted' => 'info',
                    'interested' => 'success',
                    'rejected' => 'danger',
                    'converted' => 'success',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('priority')->label('Prioridad')->sortable()->badge()->color(fn (string $state): string => match ($state) {
                    'high' => 'danger',
                    'medium' => 'warning',
                    'low' => 'success',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('source')->label('Fuente')->sortable()->badge(),
                Tables\Columns\TextColumn::make('contact_type')->label('Canal')->sortable()->badge(),
                Tables\Columns\TextColumn::make('created_at')->label('Creado')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Estado')->options([
                    'pending' => 'Pendiente',
                    'contacted' => 'Contactado',
                    'interested' => 'Interesado',
                    'rejected' => 'Rechazado',
                    'converted' => 'Convertido',
                ]),
                Tables\Filters\SelectFilter::make('priority')->label('Prioridad')->options([
                    'low' => 'Baja',
                    'medium' => 'Media',
                    'high' => 'Alta',
                ]),
                Tables\Filters\SelectFilter::make('source')->label('Fuente')->options([
                    'app' => 'App',
                    'web' => 'Web',
                    'manual' => 'Manual',
                ]),
                Tables\Filters\SelectFilter::make('contact_type')->label('Canal')->options([
                    'whatsapp' => 'WhatsApp',
                    'phone' => 'Teléfono',
                    'email' => 'Correo',
                ]),
                Tables\Filters\Filter::make('pending')->label('Pendientes')->query(fn ($q) => $q->where('status', 'pending'))->toggle(),
                Tables\Filters\Filter::make('converted')->label('Convertidos')->query(fn ($q) => $q->where('status', 'converted'))->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('mark_contacted')
                    ->label('Contactar')
                    ->icon('heroicon-o-phone')
                    ->color('info')
                    ->action(fn ($record) => $record->update(['status' => 'contacted']))
                    ->hidden(fn ($record) => $record->status === 'contacted' || $record->status === 'converted'),
                Tables\Actions\Action::make('mark_converted')
                    ->label('Convertir')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn ($record) => $record->update(['status' => 'converted']))
                    ->hidden(fn ($record) => $record->status === 'converted' || $record->status === 'rejected'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_status_contacted')
                        ->label('Marcar contactados')
                        ->action(fn ($records) => $records->each->update(['status' => 'contacted']))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->icon('heroicon-o-phone'),
                    Tables\Actions\BulkAction::make('mark_status_converted')
                        ->label('Marcar convertidos')
                        ->action(fn ($records) => $records->each->update(['status' => 'converted']))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->icon('heroicon-o-check-circle'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeads::route('/'),
            'create' => Pages\CreateLead::route('/create'),
            'edit' => Pages\EditLead::route('/{record}/edit'),
        ];
    }
}
