<?php

namespace App\Filament\Resources\Chapters\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChapterRolesRelationManager extends RelationManager
{
    protected static string $relationship = 'roles';

    protected static ?string $title = 'Ruoli';

    protected static ?string $label = 'Ruolo';

    protected static ?string $pluralLabel = 'Ruoli';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nome ruolo')
                ->placeholder('es. Presidente, Tesoriere, Segretario…')
                ->required()
                ->maxLength(100)
                ->columnSpanFull(),
            TextInput::make('sort_order')
                ->label('Ordine')
                ->numeric()
                ->default(0)
                ->helperText('Numero più basso = mostrato prima.'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->label('Ruolo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('Ordine')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()->label('Aggiungi ruolo'),
            ])
            ->recordActions([
                EditAction::make()->label('Modifica'),
                DeleteAction::make()->label('Elimina'),
            ])
            ->bulkActions([
                DeleteBulkAction::make()->label('Elimina selezionati'),
            ]);
    }
}
