<?php

namespace App\Filament\Resources\MemberOnepages;

use App\Enums\OnepageVisibility;
use App\Filament\Resources\MemberOnepages\Pages\CreateMemberOnepage;
use App\Filament\Resources\MemberOnepages\Pages\EditMemberOnepage;
use App\Filament\Resources\MemberOnepages\Pages\ListMemberOnepages;
use App\Models\MemberOnepage;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MemberOnepageResource extends Resource
{
    protected static ?string $model = MemberOnepage::class;
    protected static ?string $navigationLabel = 'Pagine personali';
    protected static ?string $modelLabel = 'pagina personale';
    protected static ?string $pluralModelLabel = 'pagine personali';
    protected static string|\UnitEnum|null $navigationGroup = 'Membri';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            // ── IMPOSTAZIONI ──────────────────────────────────────────────────
            Select::make('user_id')
                ->label('Membro')
                ->relationship('user', 'name')
                ->required(),
            TextInput::make('slug')
                ->label('Slug')
                ->required(),
            TextInput::make('title')
                ->label('Titolo pagina'),
            Select::make('visibility')
                ->label('Visibilità')
                ->options(OnepageVisibility::options())
                ->required(),
            Toggle::make('is_active')
                ->label('Attiva')
                ->required(),

            // ── HERO ──────────────────────────────────────────────────────────
            TextInput::make('hero_title')
                ->label('Nome visualizzato (hero)')
                ->helperText('Se vuoto usa il nome dell\'utente.'),
            TextInput::make('hero_subtitle')
                ->label('Sottotitolo hero')
                ->helperText('Se vuoto usa il nome dell\'azienda del profilo.'),
            Textarea::make('intro_text')
                ->label('Testo breve sotto il nome (corsivo)')
                ->helperText('Se vuoto usa la bio breve del profilo membro.')
                ->rows(2)
                ->columnSpanFull(),

            // ── CONTENUTI — sezione "Chi sono" ────────────────────────────────
            Textarea::make('about_text')
                ->label('Chi sono')
                ->helperText('Visualizzata nella sezione "Chi sono" del profilo pubblico. Se vuoto usa la bio del profilo membro.')
                ->rows(6)
                ->columnSpanFull(),

            // ── CONTENUTI — sezione "Servizi" ─────────────────────────────────
            Textarea::make('services_text')
                ->label('Servizi')
                ->helperText('Visualizzata nella sezione "Servizi" del profilo pubblico. Se vuoto usa i servizi del profilo membro.')
                ->rows(4)
                ->columnSpanFull(),

            // ── CONTENUTI — sezione "Competenze" (da MemberProfile.skills) ────
            Textarea::make('profile_skills')
                ->label('Competenze')
                ->helperText('Visualizzata nella sezione "Competenze" del profilo pubblico. Campo skills del profilo membro (separare con virgola).')
                ->rows(3)
                ->columnSpanFull()
                ->afterStateHydrated(function (Textarea $component, ?MemberOnepage $record): void {
                    $component->state($record?->profile?->skills);
                }),

            // ── CONTENUTI — sezione "Obiettivi di networking" (da MemberProfile) ─
            Textarea::make('profile_networking_goals')
                ->label('Obiettivi di networking')
                ->helperText('Visualizzata nella sezione "Obiettivi di networking" del profilo pubblico. Campo networking_goals del profilo membro.')
                ->rows(3)
                ->columnSpanFull()
                ->afterStateHydrated(function (Textarea $component, ?MemberOnepage $record): void {
                    $component->state($record?->profile?->networking_goals);
                }),

            // ── CALL TO ACTION ────────────────────────────────────────────────
            Textarea::make('cta_text')
                ->label('Call to action')
                ->rows(2)
                ->columnSpanFull(),

            // ── IMMAGINE BANNER ───────────────────────────────────────────────
            FileUpload::make('cover_image')
                ->label('Banner (immagine copertina)')
                ->helperText('L\'editor si apre automaticamente al caricamento per ritagliare al rapporto 4:1. Immagine salvata a 1500 × 375 px.')
                ->image()
                ->imageEditor()
                ->imageAspectRatio('4:1')
                ->automaticallyCropImagesToAspectRatio()
                ->automaticallyOpenImageEditorForAspectRatio()
                ->automaticallyResizeImagesToWidth('1500')
                ->automaticallyResizeImagesToHeight('375')
                ->automaticallyResizeImagesMode('cover')
                ->imageEditorViewportWidth(760)
                ->imageEditorViewportHeight(190)
                ->disk('public')
                ->directory('members/covers')
                ->visibility('public'),

            // ── TEMPLATE ──────────────────────────────────────────────────────
            TextInput::make('template')
                ->label('Template')
                ->default('default'),

            // ── SEO ───────────────────────────────────────────────────────────
            TextInput::make('seo_title')
                ->label('SEO title'),
            Textarea::make('seo_description')
                ->label('SEO description')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Membro')
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),
                TextColumn::make('title')
                    ->label('Titolo')
                    ->searchable(),
                TextColumn::make('visibility')
                    ->label('Visibilita')
                    ->badge()
                    ->formatStateUsing(fn (OnepageVisibility|string|null $state) => $state instanceof OnepageVisibility ? $state->label() : $state),
                IconColumn::make('is_active')
                    ->label('Attiva')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Aggiornata')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
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
            'index' => ListMemberOnepages::route('/'),
            'create' => CreateMemberOnepage::route('/create'),
            'edit' => EditMemberOnepage::route('/{record}/edit'),
        ];
    }
}
