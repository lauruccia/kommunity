<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Models\Page;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationLabel = 'Pagine CMS';
    protected static ?string $modelLabel = 'Pagina';
    protected static ?string $pluralModelLabel = 'Pagine';
    protected static string|\UnitEnum|null $navigationGroup = 'Sito';
    protected static ?int $navigationSort = 10;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Contenuto pagina')
                ->components([
                    TextInput::make('title')
                        ->label('Titolo')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (string $operation, $state, callable $set) {
                            if ($operation === 'create') {
                                $set('slug', Str::slug($state));
                            }
                        }),

                    TextInput::make('slug')
                        ->label('Slug URL')
                        ->required()
                        ->unique(Page::class, 'slug', ignoreRecord: true)
                        ->helperText('Usato nell\'URL: /pagina/{slug}')
                        ->maxLength(255),

                    RichEditor::make('content')
                        ->label('Contenuto')
                        ->columnSpanFull()
                        ->toolbarButtons([
                            'attachFiles',
                            'blockquote',
                            'bold',
                            'bulletList',
                            'codeBlock',
                            'h2',
                            'h3',
                            'italic',
                            'link',
                            'orderedList',
                            'redo',
                            'strike',
                            'underline',
                            'undo',
                        ]),

                    Textarea::make('meta_description')
                        ->label('Meta description SEO')
                        ->rows(2)
                        ->maxLength(320)
                        ->columnSpanFull(),
                ]),

            Section::make('Pubblicazione e menu')
                ->columns(2)
                ->components([
                    Toggle::make('is_published')
                        ->label('Pubblicata')
                        ->helperText('Solo le pagine pubblicate sono visibili sul sito'),

                    Toggle::make('show_in_nav')
                        ->label('Mostra nel menu in alto')
                        ->helperText('Quando spento, la pagina resta accessibile dal suo URL ma non appare nel menu superiore.'),

                    Toggle::make('show_in_footer')
                        ->label('Mostra nel footer'),

                    TextInput::make('nav_order')
                        ->label('Ordine nel nav')
                        ->numeric()
                        ->default(0)
                        ->helperText('Numero più basso = prima posizione'),

                    TextInput::make('footer_order')
                        ->label('Ordine nel footer')
                        ->numeric()
                        ->default(0),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Titolo')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label('URL')
                    ->formatStateUsing(fn ($state) => '/pagina/' . $state)
                    ->copyable()
                    ->color('gray'),

                IconColumn::make('is_published')
                    ->label('Pubblicata')
                    ->boolean(),

                IconColumn::make('show_in_nav')
                    ->label('Nel nav')
                    ->boolean(),

                IconColumn::make('show_in_footer')
                    ->label('Nel footer')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('Ultima modifica')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('nav_order')
            ->filters([
                TernaryFilter::make('is_published')
                    ->label('Pubblicazione')
                    ->trueLabel('Solo pubblicate')
                    ->falseLabel('Solo bozze')
                    ->native(false),

                TernaryFilter::make('show_in_nav')
                    ->label('Menu in alto')
                    ->trueLabel('Visibili nel menu')
                    ->falseLabel('Nascoste dal menu')
                    ->native(false),
            ])
            ->recordActions([
                Action::make('showInNav')
                    ->label('Mostra nel menu')
                    ->icon(Heroicon::OutlinedEye)
                    ->color('success')
                    ->visible(fn (Page $record): bool => ! $record->show_in_nav)
                    ->action(fn (Page $record): bool => $record->update(['show_in_nav' => true])),

                Action::make('hideFromNav')
                    ->label('Nascondi dal menu')
                    ->icon(Heroicon::OutlinedEyeSlash)
                    ->color('warning')
                    ->visible(fn (Page $record): bool => (bool) $record->show_in_nav)
                    ->action(fn (Page $record): bool => $record->update(['show_in_nav' => false])),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('showSelectedInNav')
                        ->label('Mostra nel menu in alto')
                        ->icon(Heroicon::OutlinedEye)
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records): int => Page::query()
                            ->whereKey($records->modelKeys())
                            ->update(['show_in_nav' => true])),

                    BulkAction::make('hideSelectedFromNav')
                        ->label('Nascondi dal menu in alto')
                        ->icon(Heroicon::OutlinedEyeSlash)
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records): int => Page::query()
                            ->whereKey($records->modelKeys())
                            ->update(['show_in_nav' => false])),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }
}
