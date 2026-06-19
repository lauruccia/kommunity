<?php

namespace App\Filament\Resources\AvailabilitySlots;

use App\Filament\Resources\AvailabilitySlots\Pages\CreateAvailabilitySlot;
use App\Filament\Resources\AvailabilitySlots\Pages\EditAvailabilitySlot;
use App\Filament\Resources\AvailabilitySlots\Pages\ListAvailabilitySlots;
use App\Models\AvailabilitySlot;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AvailabilitySlotResource extends Resource
{
    protected static ?string $model = AvailabilitySlot::class;
    protected static ?string $navigationLabel = 'Disponibilita';
    protected static ?string $modelLabel = 'slot disponibilita';
    protected static ?string $pluralModelLabel = 'slot disponibilita';
    protected static string|\UnitEnum|null $navigationGroup = 'Relazioni';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')->label('Utente')->relationship('user', 'name')->required(),
            Select::make('weekday')->label('Giorno')->options([
                1 => 'Lunedi',
                2 => 'Martedi',
                3 => 'Mercoledi',
                4 => 'Giovedi',
                5 => 'Venerdi',
                6 => 'Sabato',
                7 => 'Domenica',
            ])->required(),
            TimePicker::make('starts_at')->label('Dalle')->seconds(false)->required(),
            TimePicker::make('ends_at')->label('Alle')->seconds(false)->required(),
            TextInput::make('timezone')->label('Timezone')->default('Europe/Rome')->required(),
            Select::make('meeting_mode')->label('Modalita')->options([
                'online' => 'Online',
                'in_person' => 'In presenza',
            ])->required(),
            TextInput::make('location')->label('Luogo / nota'),
            Toggle::make('is_active')->label('Attivo')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Utente')->searchable(),
                TextColumn::make('weekday')->label('Giorno')->formatStateUsing(fn ($state) => [
                    1 => 'Lunedi', 2 => 'Martedi', 3 => 'Mercoledi', 4 => 'Giovedi', 5 => 'Venerdi', 6 => 'Sabato', 7 => 'Domenica',
                ][$state] ?? $state),
                TextColumn::make('starts_at')->label('Dalle'),
                TextColumn::make('ends_at')->label('Alle'),
                TextColumn::make('meeting_mode')->label('Modalita')->formatStateUsing(fn ($state) => $state === 'online' ? 'Online' : 'In presenza'),
                IconColumn::make('is_active')->label('Attivo')->boolean(),
            ])
            ->filters([])
            ->recordActions([
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
            'index' => ListAvailabilitySlots::route('/'),
            'create' => CreateAvailabilitySlot::route('/create'),
            'edit' => EditAvailabilitySlot::route('/{record}/edit'),
        ];
    }
}
