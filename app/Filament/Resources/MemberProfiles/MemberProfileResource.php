<?php

namespace App\Filament\Resources\MemberProfiles;

use App\Enums\ContactMethod;
use App\Enums\MemberProfileStatus;
use App\Filament\Resources\MemberProfiles\Pages\CreateMemberProfile;
use App\Filament\Resources\MemberProfiles\Pages\EditMemberProfile;
use App\Filament\Resources\MemberProfiles\Pages\ListMemberProfiles;
use App\Filament\Resources\MemberProfiles\Pages\ViewMemberProfile;
use App\Models\Chapter;
use App\Models\MemberProfile;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Illuminate\Support\Facades\DB;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MemberProfileResource extends Resource
{
    protected static ?string $model = MemberProfile::class;
    protected static ?string $navigationLabel = 'Profili membri';
    protected static ?string $modelLabel = 'profilo membro';
    protected static ?string $pluralModelLabel = 'profili membri';
    protected static string|\UnitEnum|null $navigationGroup = 'Membri';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Membro')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('company_name')->label('Azienda'),
                Select::make('profession_id')
                    ->label('Professione primaria (per Pianeta)')
                    ->options(fn () => \App\Models\Profession::flatTree()->pluck('label', 'id'))
                    ->searchable()
                    ->preload(),
                Select::make('professions')
                    ->label('Professioni (selezione multipla)')
                    ->options(fn () => \App\Models\Profession::flatTree()->pluck('label', 'id'))
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Select::make('categories')
                    ->label('Categorie (selezione multipla)')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Select::make('city_id')
                    ->label('Citta')
                    ->relationship('city', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('region_id')
                    ->label('Regione')
                    ->relationship('region', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('active_chapter_id')
                    ->label('Pianeta attivo (principale)')
                    ->relationship('chapter', 'name')
                    ->helperText('Il pianeta attualmente attivo per questo membro.'),
                Select::make('admin_planets')
                    ->label('Tutti i Pianeti iscritti')
                    ->options(fn () => Chapter::orderBy('name')->pluck('name', 'id'))
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->dehydrated(false)
                    ->afterStateHydrated(function (Select $component, ?MemberProfile $record): void {
                        if (! $record?->user_id) {
                            return;
                        }
                        $ids = DB::table('chapter_members')
                            ->where('user_id', $record->user_id)
                            ->pluck('chapter_id')
                            ->toArray();
                        $component->state($ids);
                    })
                    ->helperText('Aggiungi o rimuovi pianeti. Il pianeta attivo principale va impostato nel campo sopra.'),
                Select::make('companyInterestTypes')
                    ->label('Tipologie aziende/gruppi da conoscere')
                    ->relationship('companyInterestTypes', 'name')
                    ->multiple()
                    ->preload(),
                Select::make('professionsOfInterest')
                    ->label('Professionisti da conoscere (networking)')
                    ->options(fn () => \App\Models\Profession::flatTree()->pluck('label', 'id'))
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Textarea::make('bio')
                    ->label('Bio')
                    ->columnSpanFull(),
                Textarea::make('short_bio')
                    ->label('Bio breve')
                    ->columnSpanFull(),
                Textarea::make('services')
                    ->label('Servizi')
                    ->columnSpanFull(),
                Textarea::make('skills')
                    ->label('Competenze')
                    ->columnSpanFull(),
                Textarea::make('networking_goals')
                    ->label('Obiettivi di networking')
                    ->columnSpanFull(),
                Toggle::make('use_ai_profile_rewrite')
                    ->label('Usa AI per rielaborare i testi')
                    ->helperText('Quando il membro salva il profilo, i campi narrativi vengono riscritti in modo professionale.'),
                TextInput::make('website')
                    ->label('Sito web')
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn (?string $state): ?string => self::normalizeUrlField($state)),
                TextInput::make('linkedin_url')
                    ->label('LinkedIn')
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn (?string $state): ?string => self::normalizeUrlField($state)),
                TextInput::make('facebook_url')
                    ->label('Facebook')
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn (?string $state): ?string => self::normalizeUrlField($state)),
                TextInput::make('instagram_url')
                    ->label('Instagram')
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn (?string $state): ?string => self::normalizeUrlField($state)),
                TextInput::make('phone')
                    ->label('Telefono')
                    ->tel(),
                TextInput::make('whatsapp_number')->label('WhatsApp'),
                Toggle::make('show_email')
                    ->label('Mostra email')
                    ->required(),
                Toggle::make('show_phone')
                    ->label('Mostra telefono')
                    ->required(),
                Toggle::make('show_whatsapp')
                    ->label('Mostra WhatsApp')
                    ->required(),
                Toggle::make('allow_whatsapp_contact')
                    ->label('Consenti contatto WhatsApp')
                    ->required(),
                Select::make('preferred_contact_method')
                    ->label('Contatto preferito')
                    ->options(ContactMethod::options())
                    ->default('email')
                    ->required(),
                FileUpload::make('avatar')
                    ->label('Avatar')
                    ->image()
                    ->imageEditor()
                    ->disk('public')
                    ->directory('members/avatars')
                    ->visibility('public'),
                FileUpload::make('logo')
                    ->label('Logo')
                    ->image()
                    ->imageEditor()
                    ->disk('public')
                    ->directory('members/logos')
                    ->visibility('public'),
                FileUpload::make('cover_image')
                    ->label('Banner (immagine copertina profilo)')
                    ->helperText('Carica il banner nelle dimensioni originali — verrà mostrato automaticamente in proporzione. Opzionalmente usa l\'editor per ritagliare.')
                    ->image()
                    ->imageEditor()
                    ->disk('public')
                    ->directory('members/covers')
                    ->visibility('public')
                    ->afterStateHydrated(function (FileUpload $component, ?MemberProfile $record): void {
                        $component->state($record?->onepage?->cover_image);
                    }),
                FileUpload::make('intro_video')
                    ->label('Video presentazione')
                    ->acceptedFileTypes(['video/mp4', 'video/quicktime', 'video/webm'])
                    ->disk('public')
                    ->directory('members/videos')
                    ->visibility('public'),
                Select::make('intro_video_duration_minutes')
                    ->label('Durata video')
                    ->options([
                        2 => '2 minuti',
                        3 => '3 minuti',
                        5 => '5 minuti',
                    ]),
                Toggle::make('is_visible_in_directory')
                    ->label('Visibile in directory')
                    ->required(),
                Toggle::make('is_active')
                    ->label('Attivo')
                    ->required(),
                Toggle::make('onboarding_completed')
                    ->label('Onboarding completato')
                    ->required(),
                Select::make('status')
                    ->label('Stato')
                    ->options(MemberProfileStatus::options())
                    ->default('draft')
                    ->required(),
            ]);
    }

    private static function normalizeUrlField(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (! preg_match('#^https?://#i', $value)) {
            return 'https://' . $value;
        }

        return $value;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label('Membro'),
                TextEntry::make('company_name')
                    ->label('Azienda')
                    ->placeholder('-'),
                TextEntry::make('profession.name')
                    ->label('Professione primaria')
                    ->placeholder('-'),
                TextEntry::make('professions.name')
                    ->label('Professioni')
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('categories.name')
                    ->label('Categorie')
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('city.name')
                    ->label('Citta')
                    ->placeholder('-'),
                TextEntry::make('region.name')
                    ->label('Regione')
                    ->placeholder('-'),
                TextEntry::make('chapter.name')
                    ->label('Pianeta')
                    ->placeholder('-'),
                TextEntry::make('companyInterestTypes.name')
                    ->label('Tipologie aziende/gruppi da conoscere')
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('professionsOfInterest.name')
                    ->label('Professionisti da conoscere (networking)')
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('user.invited_by_name')
                    ->label('Invitato da')
                    ->placeholder('-'),
                TextEntry::make('bio')
                    ->label('Bio')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('short_bio')
                    ->label('Bio breve')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('services')
                    ->label('Servizi')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('skills')
                    ->label('Competenze')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('networking_goals')
                    ->label('Obiettivi di networking')
                    ->placeholder('-')
                    ->columnSpanFull(),
                IconEntry::make('use_ai_profile_rewrite')
                    ->label('Usa AI')
                    ->boolean(),
                TextEntry::make('website')
                    ->label('Sito web')
                    ->placeholder('-'),
                TextEntry::make('linkedin_url')
                    ->label('LinkedIn')
                    ->placeholder('-'),
                TextEntry::make('facebook_url')
                    ->label('Facebook')
                    ->placeholder('-'),
                TextEntry::make('instagram_url')
                    ->label('Instagram')
                    ->placeholder('-'),
                TextEntry::make('phone')
                    ->label('Telefono')
                    ->placeholder('-'),
                TextEntry::make('whatsapp_number')
                    ->label('WhatsApp')
                    ->placeholder('-'),
                IconEntry::make('show_email')
                    ->label('Mostra email')
                    ->boolean(),
                IconEntry::make('show_phone')
                    ->label('Mostra telefono')
                    ->boolean(),
                IconEntry::make('show_whatsapp')
                    ->label('Mostra WhatsApp')
                    ->boolean(),
                IconEntry::make('allow_whatsapp_contact')
                    ->label('Consenti contatto WhatsApp')
                    ->boolean(),
                TextEntry::make('preferred_contact_method')
                    ->label('Contatto preferito')
                    ->badge()
                    ->formatStateUsing(fn (ContactMethod|string|null $state) => $state instanceof ContactMethod ? $state->label() : $state),
                TextEntry::make('avatar')
                    ->label('Avatar')
                    ->placeholder('-'),
                TextEntry::make('logo')
                    ->label('Logo')
                    ->placeholder('-'),
                ImageEntry::make('onepage.cover_image')
                    ->label('Banner (immagine copertina profilo)')
                    ->disk('public')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('intro_video')
                    ->label('Video presentazione')
                    ->placeholder('-'),
                TextEntry::make('intro_video_duration_minutes')
                    ->label('Durata video')
                    ->suffix(' min')
                    ->placeholder('-'),
                IconEntry::make('is_visible_in_directory')
                    ->label('Visibile in directory')
                    ->boolean(),
                IconEntry::make('is_active')
                    ->label('Attivo')
                    ->boolean(),
                IconEntry::make('onboarding_completed')
                    ->label('Onboarding completato')
                    ->boolean(),
                TextEntry::make('status')
                    ->label('Stato')
                    ->badge()
                    ->formatStateUsing(fn (MemberProfileStatus|string|null $state) => $state instanceof MemberProfileStatus ? $state->label() : $state),
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
                // ── Colonne sempre visibili (essenziali per identificare il membro) ──
                TextColumn::make('user.name')
                    ->label('Membro')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('company_name')
                    ->label('Azienda')
                    ->searchable(),
                TextColumn::make('profession.name')
                    ->label('Professione')
                    ->searchable(),
                TextColumn::make('city.name')
                    ->label('Città')
                    ->searchable(),
                TextColumn::make('chapter.name')
                    ->label('Pianeta attivo')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->formatStateUsing(fn (MemberProfileStatus|string|null $state) => $state instanceof MemberProfileStatus ? $state->label() : $state)
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Attivo')
                    ->boolean(),
                // ── Colonne nascoste di default (visibili via toggle colonne) ──────
                TextColumn::make('region.name')
                    ->label('Regione')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('categories.name')
                    ->label('Categorie')
                    ->badge()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.invited_by_name')
                    ->label('Invitato da')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('phone')
                    ->label('Telefono')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('whatsapp_number')
                    ->label('WhatsApp')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('website')
                    ->label('Sito web')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('linkedin_url')
                    ->label('LinkedIn')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('facebook_url')
                    ->label('Facebook')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('instagram_url')
                    ->label('Instagram')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('preferred_contact_method')
                    ->label('Contatto preferito')
                    ->badge()
                    ->formatStateUsing(fn (ContactMethod|string|null $state) => $state instanceof ContactMethod ? $state->label() : $state)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('show_email')
                    ->label('Mostra email')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('show_phone')
                    ->label('Mostra tel.')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('show_whatsapp')
                    ->label('Mostra WA')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('allow_whatsapp_contact')
                    ->label('Contatto WA')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('use_ai_profile_rewrite')
                    ->label('AI testi')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('intro_video_duration_minutes')
                    ->label('Video')
                    ->suffix(' min')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_visible_in_directory')
                    ->label('Directory')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('onboarding_completed')
                    ->label('Onboarding')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('company_interest_types_count')
                    ->counts('companyInterestTypes')
                    ->label('Tipologie da conoscere')
                    ->tooltip(fn ($record) => $record->companyInterestTypes->pluck('name')->join(', '))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('professions_of_interest_count')
                    ->counts('professionsOfInterest')
                    ->label('Professionisti da conoscere')
                    ->tooltip(fn ($record) => $record->professionsOfInterest->pluck('name')->join(', '))
                    ->sortable()
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
            'index' => ListMemberProfiles::route('/'),
            'create' => CreateMemberProfile::route('/create'),
            'view' => ViewMemberProfile::route('/{record}'),
            'edit' => EditMemberProfile::route('/{record}/edit'),
        ];
    }
}
