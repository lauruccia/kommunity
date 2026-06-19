<?php

namespace App\Filament\Resources\Events\RelationManagers;

use App\Models\EventRegistration;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class EventRegistrationsRelationManager extends RelationManager
{
    protected static string $relationship = 'registrations';

    protected static ?string $title = 'Iscritti';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Utente')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->size('xs')
                    ->copyable(),
                TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'attending'  => 'Confermato',
                        'waitlist'   => 'Lista d\'attesa',
                        'cancelled'  => 'Cancellato',
                        default      => $state ?? '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'attending' => 'success',
                        'waitlist'  => 'warning',
                        'cancelled' => 'danger',
                        default     => 'gray',
                    }),
                TextColumn::make('registered_at')
                    ->label('Iscritto il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('reminder_sent_at')
                    ->label('Reminder inviato')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('No')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Stato')
                    ->options([
                        'attending'  => 'Confermato',
                        'waitlist'   => 'Lista d\'attesa',
                        'cancelled'  => 'Cancellato',
                    ]),
            ])
            ->recordActions([
                Action::make('rimuovi')
                    ->label('Rimuovi iscrizione')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (EventRegistration $record) => $record->delete()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Rimuovi selezionati'),
                ]),
            ])
            ->defaultSort('registered_at', 'desc');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }
}
