<?php

namespace App\Filament\Resources\OneToOneRequests;

use App\Enums\OneToOneStatus;
use App\Filament\Resources\OneToOneRequests\Pages\CreateOneToOneRequest;
use App\Filament\Resources\OneToOneRequests\Pages\EditOneToOneRequest;
use App\Filament\Resources\OneToOneRequests\Pages\ListOneToOneRequests;
use App\Filament\Resources\OneToOneRequests\Pages\ViewOneToOneRequest;
use App\Models\OneToOneRequest;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OneToOneRequestResource extends Resource
{
    protected static ?string $model = OneToOneRequest::class;
    protected static ?string $navigationLabel = 'Incontri one-to-one';
    protected static ?string $modelLabel = 'incontro one-to-one';
    protected static ?string $pluralModelLabel = 'incontri one-to-one';
    protected static string|\UnitEnum|null $navigationGroup = 'Relazioni';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('requester_id')
                    ->label('Richiedente')
                    ->relationship('requester', 'name')
                    ->required(),
                Select::make('recipient_id')
                    ->label('Destinatario')
                    ->relationship('recipient', 'name')
                    ->required(),
                TextInput::make('availability_slot_id')
                    ->label('Slot disponibilita')
                    ->numeric(),
                DateTimePicker::make('requested_at')->label('Data richiesta'),
                TextInput::make('meeting_mode')
                    ->label('Modalita')
                    ->required()
                    ->default('online'),
                TextInput::make('meeting_link')->label('Link incontro'),
                TextInput::make('meeting_location')->label('Luogo incontro'),
                Textarea::make('goal')
                    ->label('Obiettivo')
                    ->columnSpanFull(),
                Textarea::make('pre_notes')
                    ->label('Note pre incontro')
                    ->columnSpanFull(),
                Textarea::make('post_notes')
                    ->label('Note post incontro')
                    ->columnSpanFull(),
                Textarea::make('follow_up_notes')
                    ->label('Note follow-up')
                    ->columnSpanFull(),
                Select::make('status')
                    ->label('Stato')
                    ->options(OneToOneStatus::options())
                    ->default('pending')
                    ->required(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('requester.name')
                    ->label('Richiedente'),
                TextEntry::make('recipient.name')
                    ->label('Destinatario'),
                TextEntry::make('availability_slot_id')
                    ->label('Slot disponibilita')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('requested_at')
                    ->label('Data richiesta')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('meeting_mode')->label('Modalita'),
                TextEntry::make('meeting_link')
                    ->label('Link incontro')
                    ->placeholder('-'),
                TextEntry::make('meeting_location')
                    ->label('Luogo incontro')
                    ->placeholder('-'),
                TextEntry::make('goal')
                    ->label('Obiettivo')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('pre_notes')
                    ->label('Note pre incontro')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('post_notes')
                    ->label('Note post incontro')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('follow_up_notes')
                    ->label('Note follow-up')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('status')
                    ->label('Stato')
                    ->badge(),
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
                TextColumn::make('requester.name')
                    ->label('Richiedente')
                    ->searchable(),
                TextColumn::make('recipient.name')
                    ->label('Destinatario')
                    ->searchable(),
                TextColumn::make('availability_slot_id')
                    ->label('Slot')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('requested_at')
                    ->label('Data richiesta')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('meeting_mode')
                    ->label('Modalita')
                    ->searchable(),
                TextColumn::make('meeting_link')
                    ->label('Link incontro')
                    ->searchable(),
                TextColumn::make('meeting_location')
                    ->label('Luogo incontro')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->formatStateUsing(fn (OneToOneStatus|string|null $state) => $state instanceof OneToOneStatus ? $state->label() : $state)
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
            'index' => ListOneToOneRequests::route('/'),
            'create' => CreateOneToOneRequest::route('/create'),
            'view' => ViewOneToOneRequest::route('/{record}'),
            'edit' => EditOneToOneRequest::route('/{record}/edit'),
        ];
    }
}
