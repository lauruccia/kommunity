<?php

namespace App\Filament\Resources\Referrals;

use App\Enums\ReferralStatus;
use App\Filament\Resources\Referrals\Pages\CreateReferral;
use App\Filament\Resources\Referrals\Pages\EditReferral;
use App\Filament\Resources\Referrals\Pages\ListReferrals;
use App\Filament\Resources\Referrals\Pages\ViewReferral;
use App\Models\Referral;
use App\Notifications\ReferralConfirmedNotification;
use BackedEnum;
use Filament\Actions\Action;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReferralResource extends Resource
{
    protected static ?string $model = Referral::class;
    protected static ?string $navigationLabel = 'Referenze';
    protected static ?string $modelLabel = 'referenza';
    protected static ?string $pluralModelLabel = 'referenze';
    protected static string|\UnitEnum|null $navigationGroup = 'Relazioni';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationBadge(): ?string
    {
        $count = Referral::query()->whereIn('status', [
            ReferralStatus::Completed->value,
            ReferralStatus::ClientConfirmed->value,
        ])->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('sender_id')
                    ->label('Segnalatore')
                    ->relationship('sender', 'name')
                    ->searchable()
                    ->required(),
                Select::make('recipient_id')
                    ->label('Professionista')
                    ->relationship('recipient', 'name')
                    ->searchable()
                    ->required(),
                Select::make('client_user_id')
                    ->label('Cliente segnalato')
                    ->relationship('client', 'name')
                    ->searchable(),
                TextInput::make('title')
                    ->label('Titolo')
                    ->required(),
                Textarea::make('description')
                    ->label('Descrizione')
                    ->columnSpanFull(),
                TextInput::make('company_name')->label('Azienda / cliente segnalato'),
                TextInput::make('contact_name')->label('Contatto'),
                TextInput::make('estimated_value')
                    ->label('Valore stimato (€)')
                    ->numeric(),
                TextInput::make('declared_value')
                    ->label('Valore dichiarato dal professionista (€)')
                    ->numeric(),
                TextInput::make('approved_value')
                    ->label('Valore confermato dall\'admin (€)')
                    ->numeric()
                    ->helperText('Solo questo importo, con stato "Confermata", conta per la classifica.'),
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
                TextEntry::make('sender.name')->label('Segnalatore'),
                TextEntry::make('recipient.name')->label('Professionista'),
                TextEntry::make('client.name')->label('Cliente segnalato')->placeholder('-'),
                TextEntry::make('title')->label('Titolo'),
                TextEntry::make('description')->label('Descrizione')->placeholder('-')->columnSpanFull(),
                TextEntry::make('company_name')->label('Azienda / cliente')->placeholder('-'),
                TextEntry::make('contact_name')->label('Contatto')->placeholder('-'),
                TextEntry::make('estimated_value')->label('Valore stimato')->money('EUR')->placeholder('-'),
                TextEntry::make('declared_value')->label('Valore dichiarato')->money('EUR')->placeholder('-'),
                TextEntry::make('approved_value')->label('Valore confermato')->money('EUR')->placeholder('-'),
                TextEntry::make('declared_at')->label('Dichiarato il')->dateTime()->placeholder('-'),
                TextEntry::make('approvedBy.name')->label('Validato da')->placeholder('-'),
                TextEntry::make('approved_at')->label('Validato il')->dateTime()->placeholder('-'),
                TextEntry::make('status')
                    ->label('Stato')
                    ->badge()
                    ->color(fn (ReferralStatus|string|null $state) => $state instanceof ReferralStatus ? $state->filamentColor() : 'gray')
                    ->formatStateUsing(fn (ReferralStatus|string|null $state) => $state instanceof ReferralStatus ? $state->label() : $state),
                TextEntry::make('notes')->label('Note')->placeholder('-')->columnSpanFull(),
                TextEntry::make('outcome')->label('Esito')->placeholder('-')->columnSpanFull(),
                TextEntry::make('created_at')->dateTime()->placeholder('-'),
                TextEntry::make('updated_at')->dateTime()->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sender.name')->label('Segnalatore')->searchable(),
                TextColumn::make('recipient.name')->label('Professionista')->searchable(),
                TextColumn::make('client.name')->label('Cliente')->searchable()->placeholder('-'),
                TextColumn::make('title')->label('Titolo')->searchable()->limit(28),
                TextColumn::make('declared_value')->label('Dichiarato')->money('EUR')->sortable()->placeholder('-'),
                TextColumn::make('approved_value')->label('Confermato')->money('EUR')->sortable()->placeholder('-'),
                TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->color(fn (ReferralStatus|string|null $state) => $state instanceof ReferralStatus ? $state->filamentColor() : 'gray')
                    ->formatStateUsing(fn (ReferralStatus|string|null $state) => $state instanceof ReferralStatus ? $state->label() : $state),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Stato')
                    ->options(ReferralStatus::options()),
            ])
            ->recordActions([
                Action::make('approveValue')
                    ->label('Approva valore')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription(fn (Referral $record) => 'Confermi il valore dichiarato di € '.number_format((float) $record->declared_value, 2, ',', '.').'? Entrerà in classifica per '.($record->sender?->name ?? 'il segnalatore').'.')
                    ->visible(fn (Referral $record) => in_array($record->status, [ReferralStatus::Completed, ReferralStatus::ClientConfirmed], true))
                    ->action(function (Referral $record): void {
                        $record->update([
                            'approved_value' => $record->declared_value,
                            'approved_at'    => now(),
                            'approved_by'    => auth()->id(),
                            'status'         => ReferralStatus::Confirmed,
                        ]);
                        $record->loadMissing('sender');
                        $record->sender?->notify(new ReferralConfirmedNotification($record));
                    }),
                Action::make('rejectValue')
                    ->label('Rifiuta')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Referral $record) => in_array($record->status, [ReferralStatus::Completed, ReferralStatus::ClientConfirmed], true))
                    ->action(function (Referral $record): void {
                        $record->update([
                            'approved_at' => now(),
                            'approved_by' => auth()->id(),
                            'status'      => ReferralStatus::Rejected,
                        ]);
                    }),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
