<?php

namespace App\Filament\Resources\Cities;

use App\Filament\Resources\Cities\Pages\CreateCity;
use App\Filament\Resources\Cities\Pages\EditCity;
use App\Filament\Resources\Cities\Pages\ListCities;
use App\Filament\Resources\Cities\Pages\ViewCity;
use App\Models\City;
use App\Models\Province;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CityResource extends Resource
{
    protected static ?string $model = City::class;
    protected static ?string $navigationLabel = 'Citta';
    protected static ?string $modelLabel = 'citta';
    protected static ?string $pluralModelLabel = 'citta';
    protected static string|\UnitEnum|null $navigationGroup = 'Anagrafiche';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('region_id')
                    ->label('Regione')
                    ->relationship('region', 'name')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn (callable $set) => $set('province_id', null)),

                Select::make('province_id')
                    ->label('Provincia')
                    ->options(fn (callable $get) =>
                        Province::query()
                            ->when($get('region_id'), fn ($q, $regionId) => $q->where('region_id', $regionId))
                            ->orderBy('name')
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (callable $get, callable $set) {
                        $provinceId = $get('province_id');
                        if ($provinceId) {
                            $province = Province::find($provinceId);
                            if ($province) {
                                $set('region_id', $province->region_id);
                                $set('province', $province->code);
                            }
                        }
                    }),

                TextInput::make('name')
                    ->label('Nome città')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (callable $get, callable $set, ?string $state) {
                        if ($state) {
                            $province = Province::find($get('province_id'));
                            $suffix   = $province ? '-' . strtolower($province->code) : '';
                            $set('slug', \Illuminate\Support\Str::slug($state . $suffix));
                        }
                    }),

                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->unique(ignoreRecord: true),

                TextInput::make('province')
                    ->label('Sigla provincia (es. MI)')
                    ->maxLength(10),

                TextInput::make('postal_code')->label('CAP'),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('region.name')
                    ->label('Regione')
                    ->placeholder('-'),
                TextEntry::make('province.name')
                    ->label('Provincia')
                    ->placeholder('-'),
                TextEntry::make('name')->label('Nome'),
                TextEntry::make('slug')->label('Slug'),
                TextEntry::make('province')
                    ->label('Sigla')
                    ->placeholder('-'),
                TextEntry::make('postal_code')
                    ->label('CAP')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('region.name')
                    ->label('Regione')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('province.name')
                    ->label('Provincia')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('province')
                    ->label('Sigla')
                    ->searchable(),
                TextColumn::make('postal_code')
                    ->label('CAP')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make(),
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
            'index' => ListCities::route('/'),
            'create' => CreateCity::route('/create'),
            'view' => ViewCity::route('/{record}'),
            'edit' => EditCity::route('/{record}/edit'),
        ];
    }
}
