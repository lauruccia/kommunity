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
                    ->relationship('profession', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('professions')
                    ->label('Professioni (selezione multipla)')
                    ->relationship('professions', 'name')
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
                Select::make('all_planets_display')
                    ->label('Tutti i Pianeti iscritti')
                    ->options(fn () => Chapter::orderBy('name')->pluck('name', 'id'))
                    ->multiple()
                    ->disabled()
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
                    ->helperText('Solo lettura — gestisci le iscrizioni dalla scheda Pianeti nell\'utente.'),
                Select::make('companyInterestTypes')
                    ->label('Tipologie aziende/gruppi da conoscere')
                    ->relationship('companyInterestTypes', 'name')
                    ->multiple()
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
                TextInput::make('website')
                    ->label('Sito web')
                    ->url(),
                TextInput::make('linkedin_url')
                    ->label('LinkedIn')
                    ->url(),
                TextInput::make('facebook_url')
                    ->label('Facebook')
                    ->url(),
                TextInput::make('instagram_url')
                    ->label('Instagram')
                    ->url(),
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
                    ->label('Capitolo')
                    ->placeholder('-'),
                TextEntry::make('companyInterestTypes.name')
                    ->label('Tipologie da conoscere')
                    ->badge(),
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
                TextColumn::make('user.name')
                    ->label('Membro')
                    ->searchable(),
                TextColumn::make('company_name')
                    ->label('Azienda')
                    ->searchable(),
                TextColumn::make('profession.name')
                    ->label('Professione')
                    ->searchable(),
                TextColumn::make('categories.name')
                    ->label('Categorie')
                    ->badge()
                    ->searchable(),
                TextColumn::make('city.name')
                    ->label('Citta')
                    ->searchable(),
                TextColumn::make('region.name')
                    ->label('Regione')
                    ->searchable(),
                TextColumn::make('chapter.name')
                    ->label('Capitolo')
                    ->searchable(),
                TextColumn::make('companyInterestTypes.name')
                    ->label('Tipologie da conoscere')
                    ->badge(),
                TextColumn::make('user.invited_by_name')
                    ->label('Invitato da')
                    ->searchable(),
                TextColumn::make('website')
                    ->label('Sito web')
                    ->searchable(),
                TextColumn::make('linkedin_url')
                    ->label('LinkedIn')
                    ->searchable(),
                TextColumn::make('facebook_url')
                    ->label('Facebook')
                    ->searchable(),
                TextColumn::make('instagram_url')
                    ->label('Instagram')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Telefono')
                    ->searchable(),
                TextColumn::make('whatsapp_number')
                    ->label('WhatsApp')
                    ->searchable(),
                IconColumn::make('show_email')
                    ->label('Email')
                    ->boolean(),
                IconColumn::make('show_phone')
                    ->label('Telefono')
                    ->boolean(),
                IconColumn::make('show_whatsapp')
                    ->label('WhatsApp')
                    ->boolean(),
                IconColumn::make('allow_whatsapp_contact')
                    ->label('Contatto WA')
                    ->boolean(),
                TextColumn::make('preferred_contact_method')
                    ->label('Contatto preferito')
                    ->badge()
                    ->formatStateUsing(fn (ContactMethod|string|null $state) => $state instanceof ContactMethod ? $state->label() : $state)
                    ->searchable(),
                TextColumn::make('avatar')
                    ->label('Avatar')
                    ->searchable(),
                TextColumn::make('logo')
                    ->label('Logo')
                    ->searchable(),
                TextColumn::make('intro_video_duration_minutes')
                    ->label('Video')
                    ->suffix(' min')
                    ->sortable(),
                IconColumn::make('is_visible_in_directory')
                    ->label('Directory')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('Attivo')
                    ->boolean(),
                IconColumn::make('onboarding_completed')
                    ->label('Onboarding')
                    ->boolean(),
                TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->formatStateUsing(fn (MemberProfileStatus|string|null $state) => $state instanceof MemberProfileStatus ? $state->label() : $state)
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
            'index' => ListMemberProfiles::route('/'),
            'create' => CreateMemberProfile::route('/create'),
            'view' => ViewMemberProfile::route('/{record}'),
            'edit' => EditMemberProfile::route('/{record}/edit'),
        ];
    }
}
