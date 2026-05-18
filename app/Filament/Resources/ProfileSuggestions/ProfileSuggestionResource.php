<?php

namespace App\Filament\Resources\ProfileSuggestions;

use App\Filament\Resources\ProfileSuggestions\Pages\EditProfileSuggestion;
use App\Filament\Resources\ProfileSuggestions\Pages\ListProfileSuggestions;
use App\Models\Category;
use App\Models\City;
use App\Models\CompanyInterestType;
use App\Models\Profession;
use App\Models\ProfileSuggestion;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProfileSuggestionResource extends Resource
{
    protected static ?string $model = ProfileSuggestion::class;
    protected static ?string $navigationLabel = 'Suggerimenti profilo';
    protected static ?string $modelLabel = 'suggerimento profilo';
    protected static ?string $pluralModelLabel = 'suggerimenti profilo';
    protected static string|\UnitEnum|null $navigationGroup = 'Kommunity';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLightBulb;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super-admin', 'admin-community']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('type')
                ->label('Tipo')
                ->options(self::typeOptions())
                ->disabled(),
            TextInput::make('value')
                ->label('Voce proposta')
                ->disabled(),
            Textarea::make('notes')
                ->label('Note utente')
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
                TextColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state) => self::typeOptions()[$state] ?? 'Altro')
                    ->badge(),
                TextColumn::make('value')
                    ->label('Voce proposta')
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
                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')->label('Tipo')->options(self::typeOptions()),
                SelectFilter::make('status')->label('Stato')->options([
                    'pending' => 'In valutazione',
                    'approved' => 'Approvata',
                    'rejected' => 'Rifiutata',
                ]),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approva e aggiungi')
                    ->icon(Heroicon::OutlinedCheck)
                    ->color('success')
                    ->visible(fn (ProfileSuggestion $record) => $record->status === 'pending')
                    ->action(function (ProfileSuggestion $record): void {
                        $created = self::createSuggestedRecord($record);

                        $record->update([
                            'status' => 'approved',
                            'created_record_id' => $created?->getKey(),
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);
                    }),
                Action::make('reject')
                    ->label('Rifiuta')
                    ->icon(Heroicon::OutlinedXMark)
                    ->color('danger')
                    ->visible(fn (ProfileSuggestion $record) => $record->status === 'pending')
                    ->form([
                        Textarea::make('admin_notes')
                            ->label('Motivazione')
                            ->required(),
                    ])
                    ->action(fn (ProfileSuggestion $record, array $data) => $record->update([
                        'status' => 'rejected',
                        'admin_notes' => $data['admin_notes'],
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
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
            'index' => ListProfileSuggestions::route('/'),
            'edit' => EditProfileSuggestion::route('/{record}/edit'),
        ];
    }

    private static function typeOptions(): array
    {
        return [
            'profession' => 'Professione',
            'category' => 'Categoria',
            'city' => 'Citta',
            'company_interest_type' => 'Tipologia azienda/gruppo',
            'other' => 'Altro',
        ];
    }

    private static function createSuggestedRecord(ProfileSuggestion $record): ?Model
    {
        $name = trim($record->value);
        $slug = Str::slug($name);

        if ($name === '' || $slug === '') {
            return null;
        }

        return match ($record->type) {
            'profession' => Profession::query()->firstOrCreate(['slug' => $slug], ['name' => $name, 'is_active' => true]),
            'category' => Category::query()->firstOrCreate(['slug' => $slug], ['name' => $name, 'is_active' => true]),
            'city' => City::query()->firstOrCreate(['slug' => $slug], ['name' => $name]),
            'company_interest_type' => CompanyInterestType::query()->firstOrCreate(['slug' => $slug], ['name' => $name, 'is_active' => true]),
            default => null,
        };
    }
}
