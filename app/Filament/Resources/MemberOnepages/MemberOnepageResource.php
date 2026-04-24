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
            Select::make('user_id')
                ->label('Membro')
                ->relationship('user', 'name')
                ->required(),
            TextInput::make('slug')
                ->label('Slug')
                ->required(),
            TextInput::make('title')
                ->label('Titolo'),
            TextInput::make('hero_title')
                ->label('Titolo hero'),
            TextInput::make('hero_subtitle')
                ->label('Sottotitolo hero'),
            Textarea::make('intro_text')
                ->label('Testo introduttivo')
                ->columnSpanFull(),
            Textarea::make('about_text')
                ->label('Chi sono')
                ->columnSpanFull(),
            Textarea::make('services_text')
                ->label('Servizi')
                ->columnSpanFull(),
            Textarea::make('cta_text')
                ->label('Call to action')
                ->columnSpanFull(),
            FileUpload::make('cover_image')
                ->label('Immagine copertina')
                ->image()
                ->imageEditor()
                ->disk('public')
                ->directory('members/covers')
                ->visibility('public'),
            TextInput::make('template')
                ->label('Template')
                ->default('default'),
            Select::make('visibility')
                ->label('Visibilita')
                ->options(OnepageVisibility::options())
                ->required(),
            Toggle::make('is_active')
                ->label('Attiva')
                ->required(),
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
