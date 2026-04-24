<?php

namespace App\Filament\Resources\Referrals;

use App\Enums\ReferralStatus;
use App\Filament\Resources\Referrals\Pages\CreateReferral;
use App\Filament\Resources\Referrals\Pages\EditReferral;
use App\Filament\Resources\Referrals\Pages\ListReferrals;
use App\Filament\Resources\Referrals\Pages\ViewReferral;
use App\Models\Referral;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReferralResource extends Resource
{
    protected static ?string $model = Referral::class;
    protected static ?string $navigationLabel = 'Referenze';
    protected static ?string $modelLabel = 'referenza';
    protected static ?string $pluralModelLabel = 'referenze';
    protected static string|\UnitEnum|null $navigationGroup = 'Relazioni';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('sender_id')
                    ->label('Mittente')
                    ->relationship('sender', 'name')
                    ->required(),
                Select::make('recipient_id')
                    ->label('Destinatario')
                    ->relationship('recipient', 'name')
                    ->required(),
                TextInput::make('title')
                    ->label('Titolo')
                    ->required(),
                Textarea::make('description')
                    ->label('Descrizione')
                    ->columnSpanFull(),
                TextInput::make('company_name')->label('Azienda'),
                TextInput::make('contact_name')->label('Contatto'),
                TextInput::make('estimated_value')
                    ->label('Valore stimato')
                    ->numeric(),
                TextInput::make('priority')
                    ->label('Priorita')
                    ->required()
                    ->default('medium'),
                Select::make('status')
                    ->label('Stato')
                    ->options(ReferralStatus::options())
                    ->default('sent')
                    ->required(),
                Textarea::make('notes')
                    ->label('Note')
                    ->columnSpanFull(),
                Textarea::make('outcome')
                    ->label('Esito')
                    ->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('sender.name')
                    ->label('Mittente'),
                TextEntry::make('recipient.name')
                    ->label('Destinatario'),
                TextEntry::make('title')->label('Titolo'),
                TextEntry::make('description')
                    ->label('Descrizione')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('company_name')
                    ->label('Azienda')
                    ->placeholder('-'),
                TextEntry::make('contact_name')
                    ->label('Contatto')
                    ->placeholder('-'),
                TextEntry::make('estimated_value')
                    ->label('Valore stimato')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('priority')->label('Priorita'),
                TextEntry::make('status')
                    ->label('Stato')
                    ->badge()
                    ->formatStateUsing(fn (ReferralStatus|string|null $state) => $state instanceof ReferralStatus ? $state->label() : $state),
                TextEntry::make('notes')
                    ->label('Note')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('outcome')
                    ->label('Esito')
                    ->placeholder('-')
                    ->columnSpanFull(),
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
                TextColumn::make('sender.name')
                    ->label('Mittente')
                    ->searchable(),
                TextColumn::make('recipient.name')
                    ->label('Destinatario')
                    ->searchable(),
                TextColumn::make('title')
                    ->label('Titolo')
                    ->searchable(),
                TextColumn::make('company_name')
                    ->label('Azienda')
                    ->searchable(),
                TextColumn::make('contact_name')
                    ->label('Contatto')
                    ->searchable(),
                TextColumn::make('estimated_value')
                    ->label('Valore stimato')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('priority')
                    ->label('Priorita')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->formatStateUsing(fn (ReferralStatus|string|null $state) => $state instanceof ReferralStatus ? $state->label() : $state)
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
            'index' => ListReferrals::route('/'),
            'create' => CreateReferral::route('/create'),
            'view' => ViewReferral::route('/{record}'),
            'edit' => EditReferral::route('/{record}/edit'),
        ];
    }
}
