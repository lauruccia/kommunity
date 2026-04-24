<?php

namespace App\Filament\Resources\ForumThreads;

use App\Filament\Resources\ForumThreads\Pages\CreateForumThread;
use App\Filament\Resources\ForumThreads\Pages\EditForumThread;
use App\Filament\Resources\ForumThreads\Pages\ListForumThreads;
use App\Models\ForumThread;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ForumThreadResource extends Resource
{
    protected static ?string $model = ForumThread::class;
    protected static ?string $navigationLabel = 'Discussioni forum';
    protected static ?string $modelLabel = 'discussione forum';
    protected static ?string $pluralModelLabel = 'discussioni forum';
    protected static string|\UnitEnum|null $navigationGroup = 'Community';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftEllipsis;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('forum_category_id')
                ->label('Categoria')
                ->relationship('category', 'name')
                ->required(),
            Select::make('user_id')
                ->label('Autore')
                ->relationship('user', 'name')
                ->required(),
            TextInput::make('title')->label('Titolo')->required(),
            TextInput::make('slug')->label('Slug')->required(),
            TextInput::make('excerpt')->label('Estratto'),
            Toggle::make('is_pinned')->label('In evidenza')->required(),
            Toggle::make('is_locked')->label('Bloccata')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Titolo')->searchable()->sortable(),
                TextColumn::make('category.name')->label('Categoria')->searchable(),
                TextColumn::make('user.name')->label('Autore')->searchable(),
                TextColumn::make('posts_count')->label('Post')->counts('posts'),
                IconColumn::make('is_pinned')->label('In evidenza')->boolean(),
                IconColumn::make('is_locked')->label('Bloccata')->boolean(),
                TextColumn::make('updated_at')->label('Aggiornata')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListForumThreads::route('/'),
            'create' => CreateForumThread::route('/create'),
            'edit' => EditForumThread::route('/{record}/edit'),
        ];
    }
}
