<?php

namespace App\Filament\Resources\BannerCampaigns;

use App\Filament\Resources\BannerCampaigns\Pages\CreateBannerCampaign;
use App\Filament\Resources\BannerCampaigns\Pages\EditBannerCampaign;
use App\Filament\Resources\BannerCampaigns\Pages\ListBannerCampaigns;
use App\Models\BannerCampaign;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BannerCampaignResource extends Resource
{
    protected static ?string $model = BannerCampaign::class;
    protected static ?string $navigationLabel = 'Campagne banner';
    protected static ?string $modelLabel = 'campagna banner';
    protected static ?string $pluralModelLabel = 'campagne banner';
    protected static string|\UnitEnum|null $navigationGroup = 'Banner';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super-admin', 'admin-community']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('advertiser_id')
                ->label('Inserzionista')
                ->relationship('advertiser', 'name')
                ->searchable()
                ->preload(),
            TextInput::make('name')->label('Nome campagna')->required()->maxLength(255),
            Select::make('status')
                ->label('Stato')
                ->options([
                    'draft' => 'Bozza',
                    'scheduled' => 'Programmata',
                    'active' => 'Attiva',
                    'paused' => 'In pausa',
                    'ended' => 'Terminata',
                ])
                ->default('draft')
                ->required(),
            Select::make('sales_package')
                ->label('Pacchetto commerciale')
                ->options([
                    'global' => 'Globale',
                    'planet' => 'Per Pianeta',
                    'area' => 'Per area geografica',
                    'profession' => 'Per professione',
                    'custom' => 'Custom',
                ])
                ->default('global')
                ->required(),
            DateTimePicker::make('starts_at')->label('Inizio'),
            DateTimePicker::make('ends_at')->label('Fine'),
            TextInput::make('priority')->label('Priorita')->numeric()->default(0),
            TextInput::make('price')->label('Prezzo venduto')->numeric()->prefix('€'),
            TextInput::make('max_impressions')->label('Limite impression')->numeric(),
            TextInput::make('max_clicks')->label('Limite click')->numeric(),
            TextInput::make('target_url')->label('URL destinazione')->url()->required()->columnSpanFull(),
            Toggle::make('open_in_new_tab')->label('Apri in nuova scheda')->default(true),
            Select::make('placements')
                ->label('Placement')
                ->relationship('placements', 'label')
                ->multiple()
                ->preload()
                ->required()
                ->columnSpanFull(),
            Select::make('chapters')->label('Target Pianeti')->relationship('chapters', 'name')->multiple()->preload(),
            Select::make('regions')->label('Target Regioni')->relationship('regions', 'name')->multiple()->preload(),
            Select::make('cities')->label('Target Citta')->relationship('cities', 'name')->multiple()->preload(),
            Select::make('professions')->label('Target Professioni')->relationship('professions', 'name')->multiple()->preload(),
            Select::make('categories')->label('Target Categorie')->relationship('categories', 'name')->multiple()->preload(),
            Textarea::make('notes')->label('Note interne')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['advertiser', 'chapters', 'regions', 'cities', 'professions', 'categories'])->withCount(['impressions', 'clicks']))
            ->columns([
                TextColumn::make('name')->label('Campagna')->searchable()->sortable(),
                TextColumn::make('advertiser.name')->label('Inserzionista')->searchable(),
                TextColumn::make('status')->label('Stato')->badge(),
                TextColumn::make('sales_package')
                    ->label('Pacchetto')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'global' => 'Globale',
                        'planet' => 'Pianeta',
                        'area' => 'Area',
                        'profession' => 'Professione',
                        'custom' => 'Custom',
                        default => $state ?? '-',
                    }),
                TextColumn::make('starts_at')->label('Inizio')->dateTime('d/m/Y')->sortable(),
                TextColumn::make('ends_at')->label('Fine')->dateTime('d/m/Y')->sortable(),
                TextColumn::make('impressions_count')->label('Impression')->numeric()->sortable(),
                TextColumn::make('clicks_count')->label('Click')->numeric()->sortable(),
                TextColumn::make('ctr')
                    ->label('CTR')
                    ->state(fn (BannerCampaign $record): string => $record->impressions_count > 0
                        ? number_format(($record->clicks_count / $record->impressions_count) * 100, 2, ',', '.') . '%'
                        : '0,00%'),
                TextColumn::make('target_summary')
                    ->label('Target')
                    ->state(fn (BannerCampaign $record): string => $record->targetSummary())
                    ->limit(80)
                    ->toggleable(),
            ])
            ->recordActions([
                Action::make('exportCsv')
                    ->label('Export CSV')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->url(fn (BannerCampaign $record): string => route('admin.banner-campaigns.export', $record))
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBannerCampaigns::route('/'),
            'create' => CreateBannerCampaign::route('/create'),
            'edit' => EditBannerCampaign::route('/{record}/edit'),
        ];
    }
}
