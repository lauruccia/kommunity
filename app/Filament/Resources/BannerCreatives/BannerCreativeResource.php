<?php

namespace App\Filament\Resources\BannerCreatives;

use App\Filament\Resources\BannerCreatives\Pages\CreateBannerCreative;
use App\Filament\Resources\BannerCreatives\Pages\EditBannerCreative;
use App\Filament\Resources\BannerCreatives\Pages\ListBannerCreatives;
use App\Models\BannerCreative;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BannerCreativeResource extends Resource
{
    protected static ?string $model = BannerCreative::class;
    protected static ?string $navigationLabel = 'Creativita banner';
    protected static ?string $modelLabel = 'creativita banner';
    protected static ?string $pluralModelLabel = 'creativita banner';
    protected static string|\UnitEnum|null $navigationGroup = 'Banner';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super-admin', 'admin-community']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('banner_campaign_id')
                ->label('Campagna')
                ->relationship('campaign', 'name')
                ->searchable()
                ->preload()
                ->required(),
            FileUpload::make('image_desktop')
                ->label('Immagine desktop')
                ->image()
                ->imageEditor()
                ->disk('public')
                ->directory('banners')
                ->visibility('public')
                ->required(),
            FileUpload::make('image_mobile')
                ->label('Immagine mobile')
                ->image()
                ->imageEditor()
                ->disk('public')
                ->directory('banners/mobile')
                ->visibility('public'),
            TextInput::make('alt_text')->label('Testo alternativo')->maxLength(255),
            TextInput::make('headline')->label('Headline interna')->maxLength(255),
            Select::make('placement_size')
                ->label('Formato')
                ->options([
                    'leaderboard' => 'Leaderboard',
                    'sidebar' => 'Sidebar',
                    'in_feed' => 'In feed',
                    'card' => 'Card',
                ])
                ->default('leaderboard')
                ->required(),
            Toggle::make('is_active')->label('Attiva')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_desktop')->label('Preview'),
                TextColumn::make('campaign.name')->label('Campagna')->searchable(),
                TextColumn::make('placement_size')->label('Formato')->badge(),
                TextColumn::make('alt_text')->label('Alt')->limit(40),
                IconColumn::make('is_active')->label('Attiva')->boolean(),
            ])
            ->recordActions([EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBannerCreatives::route('/'),
            'create' => CreateBannerCreative::route('/create'),
            'edit' => EditBannerCreative::route('/{record}/edit'),
        ];
    }
}
