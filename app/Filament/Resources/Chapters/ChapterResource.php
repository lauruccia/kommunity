<?php

namespace App\Filament\Resources\Chapters;

use App\Filament\Resources\Chapters\Pages\CreateChapter;
use App\Filament\Resources\Chapters\Pages\EditChapter;
use App\Filament\Resources\Chapters\Pages\ListChapters;
use App\Filament\Resources\Chapters\Pages\ViewChapter;
use App\Filament\Resources\Chapters\RelationManagers\ChapterInvitationsRelationManager;
use App\Filament\Resources\Chapters\RelationManagers\MemberProfilesRelationManager;
use App\Models\Chapter;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
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
                    ->label('Leader')
                    ->relationship('leader', 'name'),
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
                    ->label('Leader')
                    ->placeholder('-'),
                TextEntry::make('max_members_per_profession')
                    ->label('Max membri per professione'),
                IconEntry::make('enforce_profession_limit')
                    ->label('Limitazioni professionisti attive')
                    ->boolean(),
                TextEntry::make('professionDistributionSummary')
                    ->label('Distribuzione professioni nel Pianeta')
                    ->state(fn (Chapter $record) => $record->professionDistributionSummary())
                    ->columnSpanFull(),
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
                    ->label('Professionisti')
                    ->counts('memberProfiles')
                    ->sortable(),
                TextColumn::make('professionDistributionSummary')
                    ->label('Distribuzione')
                    ->state(fn (Chapter $record) => $record->professionDistributionSummary())
                    ->wrap(),
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
}
