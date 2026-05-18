<?php

namespace App\Filament\Resources\Advertisers;

use App\Filament\Resources\Advertisers\Pages\CreateAdvertiser;
use App\Filament\Resources\Advertisers\Pages\EditAdvertiser;
use App\Filament\Resources\Advertisers\Pages\ListAdvertisers;
use App\Models\Advertiser;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdvertiserResource extends Resource
{
    protected static ?string $model = Advertiser::class;
    protected static ?string $navigationLabel = 'Inserzionisti';
    protected static ?string $modelLabel = 'inserzionista';
    protected static ?string $pluralModelLabel = 'inserzionisti';
    protected static string|\UnitEnum|null $navigationGroup = 'Banner';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super-admin', 'admin-community']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nome azienda')->required()->maxLength(255),
            TextInput::make('contact_name')->label('Referente')->maxLength(255),
            TextInput::make('email')->label('Email')->email()->maxLength(255),
            TextInput::make('phone')->label('Telefono')->maxLength(50),
            Textarea::make('notes')->label('Note commerciali')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Azienda')->searchable()->sortable(),
                TextColumn::make('contact_name')->label('Referente')->searchable(),
                TextColumn::make('email')->label('Email')->searchable(),
                TextColumn::make('campaigns_count')->label('Campagne')->counts('campaigns')->sortable(),
                TextColumn::make('created_at')->label('Creato il')->dateTime('d/m/Y')->sortable(),
            ])
            ->recordActions([EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdvertisers::route('/'),
            'create' => CreateAdvertiser::route('/create'),
            'edit' => EditAdvertiser::route('/{record}/edit'),
        ];
    }
}
