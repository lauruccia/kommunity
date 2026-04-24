<?php

namespace App\Filament\Resources\Professions;

use App\Filament\Resources\Professions\Pages\CreateProfession;
use App\Filament\Resources\Professions\Pages\EditProfession;
use App\Filament\Resources\Professions\Pages\ListProfessions;
use App\Models\Profession;
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

class ProfessionResource extends Resource
{
    protected static ?string $model = Profession::class;
    protected static ?string $navigationLabel = 'Professioni';
    protected static ?string $modelLabel = 'professione';
    protected static ?string $pluralModelLabel = 'professioni';
    protected static string|\UnitEnum|null $navigationGroup = 'Anagrafiche';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

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
                TextColumn::make('member_profiles_count')->label('Profili')->counts('memberProfiles'),
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
            'index' => ListProfessions::route('/'),
            'create' => CreateProfession::route('/create'),
            'edit' => EditProfession::route('/{record}/edit'),
        ];
    }
}
