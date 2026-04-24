<?php

namespace App\Filament\Resources\Permissions;

use App\Filament\Resources\Permissions\Pages\CreatePermission;
use App\Filament\Resources\Permissions\Pages\EditPermission;
use App\Filament\Resources\Permissions\Pages\ListPermissions;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;
    protected static ?string $navigationLabel = 'Permessi';
    protected static ?string $modelLabel = 'permesso';
    protected static ?string $pluralModelLabel = 'permessi';
    protected static string|\UnitEnum|null $navigationGroup = 'Sicurezza';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super-admin', 'admin-community']) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('assegnare-permessi') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('assegnare-permessi') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('super-admin') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nome permesso')
                ->required()
                ->maxLength(255),
            CheckboxList::make('roles')
                ->label('Ruoli associati')
                ->relationship('roles', 'name')
                ->options(fn () => Role::query()->orderBy('name')->pluck('name', 'id')->all())
                ->columns(2)
                ->bulkToggleable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Permesso')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('roles_count')
                    ->label('Ruoli')
                    ->counts('roles')
                    ->sortable(),
                TextColumn::make('users_count')
                    ->label('Utenti diretti')
                    ->counts('users')
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPermissions::route('/'),
            'create' => CreatePermission::route('/create'),
            'edit' => EditPermission::route('/{record}/edit'),
        ];
    }
}
