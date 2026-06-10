<?php

namespace App\Filament\Resources\ForumThreads\RelationManagers;

use App\Models\ForumPost;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ForumPostsRelationManager extends RelationManager
{
    protected static string $relationship = 'posts';

    protected static ?string $title = 'Post';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')
                ->label('Autore')
                ->relationship('user', 'name')
                ->required(),
            Textarea::make('content')
                ->label('Contenuto')
                ->required()
                ->rows(4)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Autore')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('content')
                    ->label('Contenuto')
                    ->limit(120)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('parent.user.name')
                    ->label('Risposta a')
                    ->placeholder('Post principale')
                    ->size('xs'),
                TextColumn::make('reactions_count')
                    ->label('Reazioni')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Pubblicato')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('elimina')
                    ->label('Elimina')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Eliminare questo post?')
                    ->modalDescription('L\'operazione è irreversibile.')
                    ->action(fn (ForumPost $record) => $record->delete()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Elimina selezionati'),
                ]),
            ])
            ->defaultSort('created_at', 'asc');
    }
}
