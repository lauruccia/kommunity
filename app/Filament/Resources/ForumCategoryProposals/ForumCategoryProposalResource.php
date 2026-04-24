<?php

namespace App\Filament\Resources\ForumCategoryProposals;

use App\Filament\Resources\ForumCategoryProposals\Pages\EditForumCategoryProposal;
use App\Filament\Resources\ForumCategoryProposals\Pages\ListForumCategoryProposals;
use App\Models\ForumCategory;
use App\Models\ForumCategoryProposal;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ForumCategoryProposalResource extends Resource
{
    protected static ?string $model = ForumCategoryProposal::class;
    protected static ?string $navigationLabel = 'Proposte categorie forum';
    protected static ?string $modelLabel = 'proposta categoria forum';
    protected static ?string $pluralModelLabel = 'proposte categorie forum';
    protected static string|\UnitEnum|null $navigationGroup = 'Community';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLightBulb;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super-admin', 'admin-community']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nome proposto')
                ->disabled(),
            Textarea::make('description')
                ->label('Descrizione proposta')
                ->disabled()
                ->columnSpanFull(),
            Select::make('status')
                ->label('Stato')
                ->options([
                    'pending' => 'In valutazione',
                    'approved' => 'Approvata',
                    'rejected' => 'Rifiutata',
                ])
                ->required(),
            Textarea::make('admin_notes')
                ->label('Note admin')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Categoria proposta')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Proposta da')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'approved' => 'Approvata',
                        'rejected' => 'Rifiutata',
                        default => 'In valutazione',
                    }),
                TextColumn::make('forumCategory.name')
                    ->label('Categoria creata')
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('approva')
                    ->label('Approva')
                    ->color('success')
                    ->visible(fn (ForumCategoryProposal $record) => $record->status === 'pending')
                    ->action(function (ForumCategoryProposal $record): void {
                        $category = ForumCategory::query()->firstOrCreate(
                            ['slug' => Str::slug($record->name)],
                            [
                                'name' => $record->name,
                                'description' => $record->description,
                                'is_active' => true,
                            ],
                        );

                        $record->update([
                            'status' => 'approved',
                            'forum_category_id' => $category->id,
                        ]);
                    }),
                Action::make('rifiuta')
                    ->label('Rifiuta')
                    ->color('danger')
                    ->visible(fn (ForumCategoryProposal $record) => $record->status === 'pending')
                    ->form([
                        Textarea::make('admin_notes')
                            ->label('Motivazione')
                            ->required(),
                    ])
                    ->action(fn (ForumCategoryProposal $record, array $data) => $record->update([
                        'status' => 'rejected',
                        'admin_notes' => $data['admin_notes'],
                    ])),
                EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListForumCategoryProposals::route('/'),
            'edit' => EditForumCategoryProposal::route('/{record}/edit'),
        ];
    }
}
