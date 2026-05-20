<?php

namespace App\Filament\Resources\PlanetRoles;

use App\Filament\Resources\PlanetRoles\Pages\CreatePlanetRole;
use App\Filament\Resources\PlanetRoles\Pages\EditPlanetRole;
use App\Filament\Resources\PlanetRoles\Pages\ListPlanetRoles;
use App\Models\PlanetRole;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PlanetRoleResource extends Resource
{
    protected static ?string $model = PlanetRole::class;
    protected static ?string $navigationLabel = 'Ruoli Pianeta';
    protected static ?string $modelLabel = 'Ruolo';
    protected static ?string $pluralModelLabel = 'Ruoli Pianeta';
    protected static string|\UnitEnum|null $navigationGroup = 'Kommunity';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user?->hasAnyRole(['super-admin', 'admin-community']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nome ruolo')
                ->placeholder('es. Leader, Moderatore, Membro semplice')
                ->required()
                ->maxLength(100)
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, callable $set): void {
                    $set('slug', \Illuminate\Support\Str::slug($state));
                }),

            TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->unique(PlanetRole::class, 'slug', ignoreRecord: true)
                ->helperText('Identificatore tecnico, generato automaticamente.'),

            Textarea::make('description')
                ->label('Descrizione')
                ->placeholder('Descrivi brevemente cosa può fare questo ruolo nel pianeta.')
                ->rows(2)
                ->columnSpanFull(),

            TextInput::make('sort_order')
                ->label('Ordine')
                ->numeric()
                ->default(0)
                ->helperText('Numero più basso = mostrato prima nella selezione.'),

            CheckboxList::make('permissions')
                ->label('Permessi')
                ->helperText('Seleziona cosa può fare un membro con questo ruolo nel suo Pianeta.')
                ->options(PlanetRole::availablePermissions())
                ->columns(2)
                ->columnSpanFull()
                ->bulkToggleable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->label('Ruolo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Descrizione')
                    ->placeholder('—')
                    ->limit(60),
                TextColumn::make('permissions')
                    ->label('Permessi')
                    ->state(fn (PlanetRole $record): string =>
                        empty($record->permissions)
                            ? 'Nessuno'
                            : implode(', ', array_map(
                                fn ($p) => PlanetRole::availablePermissions()[$p] ?? $p,
                                $record->permissions
                            ))
                    )
                    ->wrap()
                    ->limit(80)
                    ->placeholder('—'),
                TextColumn::make('sort_order')
                    ->label('Ordine')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPlanetRoles::route('/'),
            'create' => CreatePlanetRole::route('/create'),
            'edit'   => EditPlanetRole::route('/{record}/edit'),
        ];
    }
}
