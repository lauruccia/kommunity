<?php

namespace App\Filament\Resources\Chapters\RelationManagers;

use App\Models\User;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChapterLeadersRelationManager extends RelationManager
{
    protected static string $relationship = 'leaders';

    protected static ?string $title = 'Leader';

    protected static ?string $label = 'Leader';

    protected static ?string $pluralLabel = 'Leader';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Aggiungi leader')
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(
                        fn ($query) => $query->orderBy('name')
                    ),
            ])
            ->recordActions([
                DetachAction::make()
                    ->label('Rimuovi')
                    ->requiresConfirmation()
                    ->modalHeading('Rimuovere il leader?')
                    ->modalDescription('Il membro non verrà eliminato, perderà solo il ruolo di leader in questo Pianeta.'),
            ])
            ->bulkActions([
                DetachBulkAction::make()->label('Rimuovi selezionati'),
            ]);
    }
}
