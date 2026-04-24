<?php

namespace App\Filament\Resources\CompanyInterestTypes;

use App\Filament\Resources\CompanyInterestTypes\Pages\CreateCompanyInterestType;
use App\Filament\Resources\CompanyInterestTypes\Pages\EditCompanyInterestType;
use App\Filament\Resources\CompanyInterestTypes\Pages\ListCompanyInterestTypes;
use App\Models\CompanyInterestType;
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

class CompanyInterestTypeResource extends Resource
{
    protected static ?string $model = CompanyInterestType::class;
    protected static ?string $navigationLabel = 'Tipologie aziende/gruppi';
    protected static ?string $modelLabel = 'tipologia azienda/gruppo';
    protected static ?string $pluralModelLabel = 'tipologie aziende/gruppi';
    protected static string|\UnitEnum|null $navigationGroup = 'Anagrafiche';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nome')
                ->required(),
            TextInput::make('slug')
                ->label('Slug')
                ->required(),
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
            ->columns([
                TextColumn::make('name')->label('Nome')->searchable()->sortable(),
                TextColumn::make('slug')->label('Slug')->searchable(),
                TextColumn::make('member_profiles_count')->label('Profili')->counts('memberProfiles'),
                IconColumn::make('is_active')->label('Attiva')->boolean(),
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
            'index' => ListCompanyInterestTypes::route('/'),
            'create' => CreateCompanyInterestType::route('/create'),
            'edit' => EditCompanyInterestType::route('/{record}/edit'),
        ];
    }
}
