<?php

namespace App\Filament\Resources\SubscriptionPlans;

use App\Enums\SubscriptionPlanType;
use App\Filament\Resources\SubscriptionPlans\Pages\CreateSubscriptionPlan;
use App\Filament\Resources\SubscriptionPlans\Pages\EditSubscriptionPlan;
use App\Filament\Resources\SubscriptionPlans\Pages\ListSubscriptionPlans;
use App\Models\SubscriptionPlan;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
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
use Illuminate\Support\Str;

class SubscriptionPlanResource extends Resource
{
    protected static ?string $model = SubscriptionPlan::class;
    protected static ?string $navigationLabel = 'Piani abbonamento';
    protected static ?string $modelLabel = 'piano';
    protected static ?string $pluralModelLabel = 'piani abbonamento';
    protected static string|\UnitEnum|null $navigationGroup = 'Abbonamenti';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super-admin', 'admin-community']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nome piano')
                ->required()
                ->maxLength(120)
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),

            TextInput::make('slug')
                ->label('Slug (identificatore)')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(120),

            Textarea::make('description')
                ->label('Descrizione')
                ->rows(3)
                ->columnSpanFull(),

            Select::make('plan_type')
                ->label('Tipo piano')
                ->options(collect(SubscriptionPlanType::cases())->mapWithKeys(
                    fn ($case) => [$case->value => $case->label()]
                ))
                ->required()
                ->default(SubscriptionPlanType::DirectoryOnly->value),

            TextInput::make('price_monthly')
                ->label('Prezzo mensile (€)')
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->prefix('€'),

            TextInput::make('price_yearly')
                ->label('Prezzo annuale (€)')
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->prefix('€'),

            TextInput::make('trial_days')
                ->label('Giorni prova gratuita')
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->suffix('giorni'),

            TextInput::make('sort_order')
                ->label('Ordinamento (più basso = prima)')
                ->numeric()
                ->default(0),

            Toggle::make('is_active')
                ->label('Piano attivo (visibile agli utenti)')
                ->default(true),

            Repeater::make('features')
                ->label('Caratteristiche piano (mostrate nella card)')
                ->schema([
                    TextInput::make('item')
                        ->label('Caratteristica')
                        ->required(),
                ])
                ->columnSpanFull()
                ->defaultItems(0)
                ->addActionLabel('Aggiungi caratteristica'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width(50),

                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('plan_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof SubscriptionPlanType ? $state->label() : $state)
                    ->color(fn ($state) => match(true) {
                        $state instanceof SubscriptionPlanType && $state === SubscriptionPlanType::DirectoryAndPage => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('price_monthly')
                    ->label('Prezzo/mese')
                    ->formatStateUsing(fn ($state) => $state == 0 ? 'Gratuito' : '€' . number_format((float)$state, 2, ',', '.')),

                TextColumn::make('price_yearly')
                    ->label('Prezzo/anno')
                    ->formatStateUsing(fn ($state) => $state == 0 ? '—' : '€' . number_format((float)$state, 2, ',', '.')),

                TextColumn::make('trial_days')
                    ->label('Prova')
                    ->formatStateUsing(fn ($state) => $state > 0 ? "{$state} gg" : '—'),

                TextColumn::make('subscriptions_count')
                    ->label('Abbonati')
                    ->counts('subscriptions'),

                IconColumn::make('is_active')
                    ->label('Attivo')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListSubscriptionPlans::route('/'),
            'create' => CreateSubscriptionPlan::route('/create'),
            'edit'   => EditSubscriptionPlan::route('/{record}/edit'),
        ];
    }
}
