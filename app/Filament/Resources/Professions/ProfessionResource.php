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
use Filament\Forms\Components\Select;
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
            Select::make('parent_id')
                ->label('Professione padre (lascia vuoto se è radice)')
                ->options(function (?Profession $record) {
                    $query = Profession::query()->orderBy('name');
                    // Esclude se stessa e i propri discendenti
                    if ($record?->id) {
                        $query->where('id', '!=', $record->id);
                    }
                    return $query->pluck('name', 'id');
                })
                ->searchable()
                ->nullable()
                ->helperText('Lascia vuoto per una professione di primo livello.'),
            TextInput::make('name')
                ->label('Nome')
                ->required(),
            TextInput::make('slug')
                ->label('Slug')
                ->required(),
            TextInput::make('sort_order')
                ->label('Ordinamento')
                ->numeric()
                ->default(0)
                ->helperText('Numero per ordinare le professioni nello stesso livello. 0 = default.'),
            Textarea::make('description')
                ->label('Descrizione')
                ->columnSpanFull(),
            Toggle::make('is_active')
                ->label('Attiva')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('parent_id', 'asc')
            ->columns([
                TextColumn::make('parent.name')
                    ->label('Categoria padre')
                    ->placeholder('(radice)')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function (Profession $record): string {
                        // Indenta visivamente i figli nella tabella
                        $prefix = $record->parent_id ? '── ' : '';
                        return $prefix . $record->name;
                    }),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sort_order')
                    ->label('Ord.')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Attiva')
                    ->boolean(),
                TextColumn::make('children_count')
                    ->label('Sotto-professioni')
                    ->counts('children')
                    ->sortable(),
                TextColumn::make('member_profiles_count')
                    ->label('Profili')
                    ->counts('memberProfiles'),
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
