<?php

namespace App\Filament\Resources\FeatureFlags;

use App\Filament\Resources\FeatureFlags\Pages\ListFeatureFlags;
use App\Models\FeatureFlag;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FeatureFlagResource extends Resource
{
    protected static ?string $model = FeatureFlag::class;

    protected static ?string $navigationLabel = 'Feature Flags';
    protected static ?string $modelLabel       = 'Feature flag';
    protected static ?string $pluralModelLabel = 'Feature flag';
    protected static string|\UnitEnum|null $navigationGroup = 'Sistema';
    protected static ?int $navigationSort = 90;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identità')
                ->columns(2)
                ->schema([
                    TextInput::make('key')
                        ->required()
                        ->disabled()      // La key non si modifica una volta creata
                        ->dehydrated()
                        ->maxLength(64)
                        ->helperText('Identificatore tecnico (non modificabile).'),
                    TextInput::make('name')
                        ->required()
                        ->maxLength(160),
                    TextInput::make('group')
                        ->required()
                        ->maxLength(64)
                        ->helperText('Gruppo logico per ordinare la lista (engagement, payments, ai, ecc.).'),
                    TextInput::make('display_order')
                        ->numeric()
                        ->default(100),
                ]),

            Section::make('Comportamento')
                ->schema([
                    Toggle::make('is_enabled')
                        ->label('Funzionalità attiva')
                        ->helperText('Quando spento, il codice gated da Features::enabled(\'key\') torna false.')
                        ->onColor('success'),
                    Textarea::make('description')
                        ->rows(3)
                        ->maxLength(500),
                    KeyValue::make('settings')
                        ->keyLabel('Chiave')
                        ->valueLabel('Valore')
                        ->reorderable()
                        ->helperText('Configurazione per-feature accessibile via Features::settings(\'key\') (es. provider AI, prezzo Stripe, dominio Jitsi…).'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('display_order')
            ->columns([
                TextColumn::make('group')->label('Gruppo')->badge()->sortable(),
                TextColumn::make('name')->label('Nome')->searchable()->sortable()->weight('bold'),
                TextColumn::make('key')->label('Key')->copyable()->fontFamily('mono')->size('xs'),
                IconColumn::make('is_enabled')->label('Attivo')->boolean(),
                TextColumn::make('description')->limit(60)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Aggiornato')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('group')
                    ->options(fn () => FeatureFlag::query()
                        ->select('group')->distinct()->pluck('group', 'group')->all()),
                SelectFilter::make('is_enabled')
                    ->options([1 => 'Attive', 0 => 'Disattive']),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFeatureFlags::route('/'),
        ];
    }
}
