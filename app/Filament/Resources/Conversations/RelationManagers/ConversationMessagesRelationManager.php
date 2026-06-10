<?php

namespace App\Filament\Resources\Conversations\RelationManagers;

use App\Models\Message;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ConversationMessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    protected static ?string $title = 'Messaggi';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Mittente')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('body')
                    ->label('Testo')
                    ->limit(150)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('attachment')
                    ->label('Allegato')
                    ->placeholder('Nessuno')
                    ->size('xs')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('read_at')
                    ->label('Letto')
                    ->boolean()
                    ->getStateUsing(fn (Message $record): bool => filled($record->read_at))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock'),
                TextColumn::make('created_at')
                    ->label('Inviato')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('elimina')
                    ->label('Elimina')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (Message $record) => $record->delete()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Elimina selezionati'),
                ]),
            ])
            ->defaultSort('created_at', 'asc');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }
}
