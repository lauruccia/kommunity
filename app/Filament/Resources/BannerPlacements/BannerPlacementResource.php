<?php

namespace App\Filament\Resources\BannerPlacements;

use App\Filament\Resources\BannerPlacements\Pages\CreateBannerPlacement;
use App\Filament\Resources\BannerPlacements\Pages\EditBannerPlacement;
use App\Filament\Resources\BannerPlacements\Pages\ListBannerPlacements;
use App\Models\BannerPlacement;
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

class BannerPlacementResource extends Resource
{
    protected static ?string $model = BannerPlacement::class;
    protected static ?string $navigationLabel = 'Placement banner';
    protected static ?string $modelLabel = 'placement banner';
    protected static ?string $pluralModelLabel = 'placement banner';
    protected static string|\UnitEnum|null $navigationGroup = 'Banner';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super-admin', 'admin-community']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('key')->label('Chiave tecnica')->required()->unique(ignoreRecord: true),
            TextInput::make('label')->label('Etichetta')->required(),
            Select::make('section')
                ->label('Sezione sito')
                ->options(self::sectionOptions())
                ->native(false)
                ->searchable()
                ->required(),
            Toggle::make('is_active')->label('Attivo')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')->label('Chiave')->searchable()->copyable(),
                TextColumn::make('label')->label('Etichetta')->searchable(),
                TextColumn::make('section')->label('Sezione')->badge(),
                IconColumn::make('is_active')->label('Attivo')->boolean(),
            ])
            ->recordActions([EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBannerPlacements::route('/'),
            'create' => CreateBannerPlacement::route('/create'),
            'edit' => EditBannerPlacement::route('/{record}/edit'),
        ];
    }

    private static function sectionOptions(): array
    {
        return [
            'directory' => 'Directory membri',
            'dashboard' => 'Dashboard',
            'events' => 'Eventi',
            'forum' => 'Forum',
            'member_profile' => 'Profilo membro',
            'one_to_ones' => 'One-to-one',
            'referrals' => 'Referral',
            'messages' => 'Messaggi',
        ];
    }
}
