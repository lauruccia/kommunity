<?php

namespace App\Filament\Resources\Chapters;

use App\Filament\Resources\Chapters\Pages\CreateChapter;
use App\Filament\Resources\Chapters\Pages\EditChapter;
use App\Filament\Resources\Chapters\Pages\ListChapters;
use App\Filament\Resources\Chapters\Pages\ViewChapter;
use App\Filament\Resources\Chapters\RelationManagers\ChapterInvitationsRelationManager;
use App\Filament\Resources\Chapters\RelationManagers\ChapterLeadersRelationManager;
use App\Filament\Resources\Chapters\RelationManagers\MemberProfilesRelationManager;
use App\Models\Chapter;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChapterResource extends Resource
{
    protected static ?string $model = Chapter::class;
    protected static ?string $navigationLabel = 'Pianeti';
    protected static ?string $modelLabel = 'Pianeta';
    protected static ?string $pluralModelLabel = 'Pianeti';
    protected static string|\UnitEnum|null $navigationGroup = 'Kommunity';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome')
                    ->required(),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required(),
                Textarea::make('description')
                    ->label('Descrizione')
                    ->columnSpanFull(),
                Select::make('city_id')
                    ->label('Citta')
                    ->relationship('city', 'name'),
                Select::make('leader_id')
                    ->label('Leader principale (legacy)')
                    ->relationship('leader', 'name')
                    ->helperText('Campo di compatibilità. Usa la tab "Leader" per gestire più leader.')
                    ->nullable(),
                Select::make('max_members_per_profession')
                    ->label('Max professionisti per categoria')
                    ->options([
                        1 => '1 membro',
                        2 => '2 membri',
                        3 => '3 membri',
                        4 => '4 membri',
                        5 => '5 membri',
                    ])
                    ->default(3)
                    ->required()
                    ->helperText('Se il limite viene superato, il membro va in lista d\'attesa automaticamente.'),
                Toggle::make('enforce_profession_limit')
                    ->label('Attiva limitazioni professionisti')
                    ->helperText('Se disattivato, il limite per categoria viene ignorato e chiunque può iscriversi.'),
                Toggle::make('is_invite_only')
                    ->label('Solo su invito')
                    ->helperText('Se attivo, solo i membri invitati direttamente possono iscriversi.'),
                FileUpload::make('cover_image')
                    ->label('Immagine copertina')
                    ->image()
                    ->imageEditor()
                    ->disk('public')
                    ->directory('chapters/covers')
                    ->visibility('public'),
                Toggle::make('is_active')
                    ->label('Attivo')
                    ->required(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')->label('Nome'),
                TextEntry::make('slug')->label('Slug'),
                TextEntry::make('description')
                    ->label('Descrizione')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('city.name')
                    ->label('Citta')
                    ->placeholder('-'),
                TextEntry::make('leader.name')
                    ->label('Leader principale (legacy)')
                    ->placeholder('-'),
                TextEntry::make('max_members_per_profession')
                    ->label('Max membri per professione'),
                IconEntry::make('enforce_profession_limit')
                    ->label('Limitazioni professionisti attive')
                    ->boolean(),
                ImageEntry::make('cover_image')
                    ->label('Copertina')
                    ->placeholder('-'),
                IconEntry::make('is_active')
                    ->label('Attivo')
                    ->boolean(),
                IconEntry::make('is_invite_only')
                    ->label('Solo su invito')
                    ->boolean(),
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
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),
                TextColumn::make('city.name')
                    ->label('Citta')
                    ->searchable(),
                TextColumn::make('leader.name')
                    ->label('Leader')
                    ->searchable(),
                TextColumn::make('max_members_per_profession')
                    ->label('Limite/professione')
                    ->sortable(),
                TextColumn::make('member_profiles_count')
                    ->label('Membri')
                    ->counts('memberProfiles')
                    ->sortable(),
                ImageColumn::make('cover_image')->label('Copertina'),
                IconColumn::make('is_active')
                    ->label('Attivo')
                    ->boolean(),
                IconColumn::make('is_invite_only')
                    ->label('Solo invito')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Action::make('statistiche')
                    ->label('Statistiche')
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->modalHeading(fn (Chapter $record): string => 'Statistiche — ' . $record->name)
                    ->modalContent(fn (Chapter $record) => self::buildStatsModal($record))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Chiudi'),
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
        return [
            ChapterLeadersRelationManager::class,
            MemberProfilesRelationManager::class,
            ChapterInvitationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChapters::route('/'),
            'create' => CreateChapter::route('/create'),
            'view' => ViewChapter::route('/{record}'),
            'edit' => EditChapter::route('/{record}/edit'),
        ];
    }

    // ── Modal statistiche ─────────────────────────────────────────────────────

    private static function buildStatsModal(Chapter $record): \Illuminate\View\View
    {
        // Distribuzione professioni
        $professions = DB::table('member_profiles')
            ->join('professions', 'professions.id', '=', 'member_profiles.profession_id')
            ->where('member_profiles.active_chapter_id', $record->id)
            ->select('professions.name', DB::raw('count(*) as total'))
            ->groupBy('professions.name')
            ->orderByDesc('total')
            ->orderBy('professions.name')
            ->get();

        // Totale membri (profili con active_chapter_id = questo pianeta)
        $totalMembers = $record->memberProfiles()->count();

        // Membri in chapter_members (appartenenza, anche non primaria)
        $totalInPivot = DB::table('chapter_members')
            ->where('chapter_id', $record->id)
            ->count();

        $activeInPivot = DB::table('chapter_members')
            ->where('chapter_id', $record->id)
            ->where('status', 'active')
            ->count();

        // Inviti
        $invitesPending  = $record->invitations()->where('status', 'pending')->count();
        $invitesAccepted = $record->invitations()->where('status', 'accepted')->count();

        // Richieste di iscrizione
        $joinPending    = $record->joinRequests()->where('status', 'pending')->count();
        $joinWaitlist   = $record->joinRequests()->where('status', 'waitlist')->count();

        $limit = $record->max_members_per_profession;
        $limitActive = $record->enforce_profession_limit;

        return view('filament.chapters.stats-modal', compact(
            'record', 'professions', 'totalMembers', 'totalInPivot',
            'activeInPivot', 'invitesPending', 'invitesAccepted',
            'joinPending', 'joinWaitlist', 'limit', 'limitActive'
        ));
    }
}
