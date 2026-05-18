<?php

namespace App\Filament\Resources\ForumCategories;

use App\Filament\Resources\ForumCategories\Pages\CreateForumCategory;
use App\Filament\Resources\ForumCategories\Pages\EditForumCategory;
use App\Filament\Resources\ForumCategories\Pages\ListForumCategories;
use App\Models\ForumCategory;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ForumCategoryResource extends Resource
{
    protected static ?string $model = ForumCategory::class;
    protected static ?string $navigationLabel = 'Categorie forum';
    protected static ?string $modelLabel = 'categoria forum';
    protected static ?string $pluralModelLabel = 'categorie forum';
    protected static string|\UnitEnum|null $navigationGroup = 'Kommunity';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleBottomCenterText;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super-admin', 'admin-community']) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasAnyRole(['super-admin', 'admin-community']) ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasAnyRole(['super-admin', 'admin-community']) ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('super-admin') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nome')->required(),
            TextInput::make('slug')->label('Slug')->required(),
            Textarea::make('description')->label('Descrizione')->columnSpanFull(),
            Toggle::make('is_active')->label('Attiva')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nome')->searchable()->sortable(),
                TextColumn::make('slug')->label('Slug')->searchable(),
                IconColumn::make('is_active')->label('Attiva')->boolean(),
                TextColumn::make('threads_count')->label('Discussioni')->counts('threads'),
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
            'index' => ListForumCategories::route('/'),
            'create' => CreateForumCategory::route('/create'),
            'edit' => EditForumCategory::route('/{record}/edit'),
        ];
    }
}
