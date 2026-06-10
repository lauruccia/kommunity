<?php

namespace App\Filament\Resources\Events\RelationManagers;

use App\Models\EventInvitation;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EventInvitationsRelationManager extends RelationManager
{
    protected static string $relationship = 'invitations';

    protected static ?string $title = 'Inviti';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Invitato')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->size('xs')
                    ->copyable(),
                TextColumn::make('invitedBy.name')
                    ->label('Invitato da')
                    ->placeholder('Sistema'),
                TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pending'   => 'In attesa',
                        'accepted'  => 'Accettato',
                        'declined'  => 'Rifiutato',
                        'notified'  => 'Notificato',
                        default     => $state ?? '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'accepted'  => 'success',
                        'declined'  => 'danger',
                        'notified'  => 'info',
                        default     => 'warning',
                    }),
                TextColumn::make('notified_at')
                    ->label('Notificato')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('No')
                    ->sortable(),
                TextColumn::make('accepted_at')
                    ->label('Accettato')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('No')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('declined_at')
                    ->label('Rifiutato')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('No')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Stato')
                    ->options([
                        'pending'  => 'In attesa',
                        'accepted' => 'Accettato',
                        'declined' => 'Rifiutato',
                        'notified' => 'Notificato',
                    ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Rimuovi selezionati'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }
}
