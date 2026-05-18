<?php

namespace App\Filament\Resources\Events;

use App\Enums\EventType;
use App\Filament\Resources\Events\Pages\CreateEvent;
use App\Filament\Resources\Events\Pages\EditEvent;
use App\Filament\Resources\Events\Pages\ListEvents;
use App\Filament\Resources\Events\Pages\ViewEvent;
use App\Models\Chapter;
use App\Models\Event;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;
    protected static ?string $navigationLabel = 'Eventi';
    protected static ?string $modelLabel = 'evento';
    protected static ?string $pluralModelLabel = 'eventi';
    protected static string|\UnitEnum|null $navigationGroup = 'Kommunity';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static function currentUser()
    {
        return auth()->user();
    }

    protected static function isAdmin(): bool
    {
        return static::currentUser()?->hasAnyRole(['super-admin', 'admin-community']) ?? false;
    }

    protected static function leaderChapterIds(): array
    {
        $user = static::currentUser();

        if (! $user) {
            return [];
        }

        return Chapter::query()
            ->where('leader_id', $user->id)
            ->pluck('id')
            ->all();
    }

    public static function canViewAny(): bool
    {
        $user = static::currentUser();

        return $user?->hasAnyRole(['super-admin', 'admin-community', 'leader-capitolo']) || $user?->can('gestire-eventi');
    }

    public static function canCreate(): bool
    {
        if (static::isAdmin()) {
            return true;
        }

        return ! empty(static::leaderChapterIds()) && (static::currentUser()?->hasRole('leader-capitolo') || static::currentUser()?->can('gestire-eventi'));
    }

    public static function canEdit($record): bool
    {
        if (static::isAdmin()) {
            return true;
        }

        return in_array($record->chapter_id, static::leaderChapterIds(), true);
    }

    public static function canDelete($record): bool
    {
        return static::canEdit($record);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (static::isAdmin()) {
            return $query;
        }

        $chapterIds = static::leaderChapterIds();

        return $query->whereIn('chapter_id', $chapterIds);
    }

    public static function form(Schema $schema): Schema
    {
        $isAdmin = static::isAdmin();
        $chapterIds = static::leaderChapterIds();

        return $schema
            ->components([
                Select::make('chapter_id')
                    ->label('Pianeta')
                    ->relationship('chapter', 'name', fn (Builder $query) => $isAdmin ? $query->orderBy('name') : $query->whereIn('id', $chapterIds)->orderBy('name'))
                    ->required()
                    ->default($chapterIds[0] ?? null)
                    ->disabled(fn () => ! $isAdmin && count($chapterIds) === 1),
                Select::make('organizer_id')
                    ->label('Organizzatore')
                    ->relationship('organizer', 'name', fn (Builder $query) => $isAdmin
                        ? $query->orderBy('name')
                        : $query->whereHas('memberProfile', fn (Builder $profileQuery) => $profileQuery->whereIn('active_chapter_id', $chapterIds))->orderBy('name'))
                    ->required()
                    ->default(static::currentUser()?->id)
                    ->disabled(fn () => ! $isAdmin),
                TextInput::make('title')
                    ->label('Titolo')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                        if (blank($get('slug'))) {
                            $set('slug', Str::slug((string) $state));
                        }
                    }),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                Textarea::make('description')
                    ->label('Descrizione')
                    ->columnSpanFull(),
                Select::make('type')
                    ->label('Tipologia')
                    ->options(EventType::options())
                    ->default('networking')
                    ->required(),
                DateTimePicker::make('starts_at')
                    ->label('Inizio')
                    ->required(),
                DateTimePicker::make('ends_at')->label('Fine'),
                TextInput::make('location')->label('Luogo'),
                TextInput::make('meeting_url')
                    ->label('Link meeting')
                    ->url(),
                TextInput::make('capacity')
                    ->label('Capienza')
                    ->numeric(),
                TextInput::make('status')
                    ->label('Stato')
                    ->required()
                    ->default('draft'),
                Toggle::make('is_published')
                    ->label('Pubblicato')
                    ->required(),
                Select::make('audience_type')
                    ->label('Audience')
                    ->options([
                        'all' => 'Tutta la community',
                        'by_planet' => 'Pianeti selezionati',
                        'by_profession' => 'Professioni selezionate',
                        'by_planet_and_profession' => 'Pianeti + Professioni',
                        'by_role' => 'Ruoli selezionati',
                        'by_planet_and_role' => 'Pianeti + Ruoli',
                        'by_profession_and_role' => 'Professioni + Ruoli',
                    ])
                    ->default('all')
                    ->required()
                    ->helperText('Usa i target sotto per restringere visibilita e partecipazione.'),
                Select::make('targetPlanets')
                    ->label('Pianeti target')
                    ->relationship('targetPlanets', 'name', fn (Builder $query) => $isAdmin ? $query->orderBy('name') : $query->whereIn('id', $chapterIds)->orderBy('name'))
                    ->multiple()
                    ->preload()
                    ->helperText('Usato dalle audience per Pianeta.'),
                Select::make('targetProfessions')
                    ->label('Professioni target')
                    ->relationship('targetProfessions', 'name', fn (Builder $query) => $query->where('is_active', true)->orderBy('name'))
                    ->multiple()
                    ->preload()
                    ->helperText('Usato dalle audience per professione.'),
                Select::make('targetRoles')
                    ->label('Ruoli target')
                    ->relationship('targetRoles', 'name', fn (Builder $query) => $query->orderBy('name'))
                    ->multiple()
                    ->preload()
                    ->helperText('Esempio: seleziona leader-capitolo per eventi riservati ai leader.'),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('chapter.name')
                    ->label('Pianeta')
                    ->placeholder('-'),
                TextEntry::make('organizer.name')
                    ->label('Organizzatore')
                    ->placeholder('-'),
                TextEntry::make('title')->label('Titolo'),
                TextEntry::make('slug')->label('Slug'),
                TextEntry::make('description')
                    ->label('Descrizione')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('type')
                    ->label('Tipologia')
                    ->badge()
                    ->formatStateUsing(fn (EventType|string|null $state) => $state instanceof EventType ? $state->label() : $state),
                TextEntry::make('starts_at')
                    ->label('Inizio')
                    ->dateTime(),
                TextEntry::make('ends_at')
                    ->label('Fine')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('location')
                    ->label('Luogo')
                    ->placeholder('-'),
                TextEntry::make('meeting_url')
                    ->label('Link meeting')
                    ->placeholder('-'),
                TextEntry::make('capacity')
                    ->label('Capienza')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('status')->label('Stato'),
                IconEntry::make('is_published')
                    ->label('Pubblicato')
                    ->boolean(),
                TextEntry::make('audience_type')
                    ->label('Audience')
                    ->formatStateUsing(fn (?string $state): string => (new Event(['audience_type' => $state ?? 'all']))->audienceLabel()),
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
                TextColumn::make('chapter.name')
                    ->label('Pianeta')
                    ->searchable(),
                TextColumn::make('organizer.name')
                    ->label('Organizzatore')
                    ->searchable(),
                TextColumn::make('title')
                    ->label('Titolo')
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Tipologia')
                    ->badge()
                    ->formatStateUsing(fn (EventType|string|null $state) => $state instanceof EventType ? $state->label() : $state)
                    ->searchable(),
                TextColumn::make('starts_at')
                    ->label('Inizio')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label('Fine')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('location')
                    ->label('Luogo')
                    ->searchable(),
                TextColumn::make('meeting_url')
                    ->label('Link meeting')
                    ->searchable(),
                TextColumn::make('capacity')
                    ->label('Capienza')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Stato')
                    ->searchable(),
                IconColumn::make('is_published')
                    ->label('Pubblicato')
                    ->boolean(),
                TextColumn::make('audience_type')
                    ->label('Audience')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => (new Event(['audience_type' => $state ?? 'all']))->audienceLabel())
                    ->toggleable(),
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
            'index' => ListEvents::route('/'),
            'create' => CreateEvent::route('/create'),
            'view' => ViewEvent::route('/{record}'),
            'edit' => EditEvent::route('/{record}/edit'),
        ];
    }
}
