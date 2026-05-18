<?php

namespace App\Filament\Resources\Users;

use App\Enums\MemberProfileStatus;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\RelationManagers\UserPlanetsRelationManager;
use App\Models\Chapter;
use App\Models\City;
use App\Models\Region;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationLabel = 'Utenti';
    protected static ?string $modelLabel = 'utente';
    protected static ?string $pluralModelLabel = 'utenti';
    protected static string|\UnitEnum|null $navigationGroup = 'Membri';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    public static function canViewAny(): bool
{
    $user = auth()->user();

    if (! $user) {
        return false;
    }

    return $user->hasAnyRole(['super-admin', 'admin-community'])
        || $user->can('gestire-utenti');
}

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nome e cognome')
                ->required()
                ->maxLength(255),
            TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->maxLength(255),
            TextInput::make('password')
                ->label('Password')
                ->password()
                ->revealable()
                ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->required(fn (string $operation): bool => $operation === 'create'),
            Select::make('roles')
                ->label('Ruoli')
                ->relationship('roles', 'name')
                ->multiple()
                ->preload(),
            CheckboxList::make('permissions')
                ->label('Permessi diretti')
                ->relationship('permissions', 'name')
                ->options(fn () => Permission::query()->orderBy('name')->pluck('name', 'id')->all())
                ->columns(2)
                ->bulkToggleable()
                ->searchable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with(['roles', 'permissions', 'memberProfile.city.region', 'memberProfile.region', 'memberProfile.chapter', 'planets', 'invitedBy'])
                ->withCount('invitedUsers'))
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('memberProfile.company_name')
                    ->label('Azienda')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('memberProfile.city.name')
                    ->label('Citta')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('memberProfile.chapter.name')
                    ->label('Pianeta')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('invitedBy.name')
                    ->label('Invitato da')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('invited_users_count')
                    ->label('Invitati')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('roles.name')
                    ->label('Ruoli')
                    ->badge(),
                TextColumn::make('email_verified_at')
                    ->label('Accesso')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => filled($state) ? 'Attivo' : 'Da attivare')
                    ->color(fn (?string $state): string => filled($state) ? 'success' : 'warning')
                    ->sortable(),
                TextColumn::make('permissions.name')
                    ->label('Permessi diretti')
                    ->badge()
                    ->limitList(3)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('memberProfile.status')
                    ->label('Stato profilo')
                    ->badge()
                    ->formatStateUsing(fn (MemberProfileStatus|string|null $state): string => $state instanceof MemberProfileStatus ? $state->label() : match ($state) {
                        'draft' => 'Bozza',
                        'pending_approval' => 'In approvazione',
                        'active' => 'Attivo',
                        'suspended' => 'Sospeso',
                        default => $state ?: '-',
                    })
                    ->color(fn (MemberProfileStatus|string|null $state): string => match ($state instanceof MemberProfileStatus ? $state->value : $state) {
                        'active' => 'success',
                        'pending_approval' => 'warning',
                        'suspended' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(),
                IconColumn::make('memberProfile.is_active')
                    ->label('Profilo attivo')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email_verified_at')
                    ->label('Verificato il')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('No')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Creato il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('planet_id')
                    ->label('Pianeta')
                    ->options(fn () => Chapter::query()
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->query(function (Builder $query, array $data): void {
                        if (blank($data['value'] ?? null)) {
                            return;
                        }

                        $query->whereHas('planets', fn (Builder $planetQuery) =>
                            $planetQuery->where('chapters.id', $data['value'])
                        );
                    }),
                SelectFilter::make('city_id')
                    ->label('Citta')
                    ->options(fn () => City::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->query(function (Builder $query, array $data): void {
                        if (blank($data['value'] ?? null)) {
                            return;
                        }

                        $query->whereHas('memberProfile', fn (Builder $profileQuery) =>
                            $profileQuery->where('city_id', $data['value'])
                        );
                    }),
                SelectFilter::make('region_id')
                    ->label('Regione')
                    ->options(fn () => Region::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->query(function (Builder $query, array $data): void {
                        if (blank($data['value'] ?? null)) {
                            return;
                        }

                        $query->whereHas('memberProfile', fn (Builder $profileQuery) =>
                            $profileQuery
                                ->where('region_id', $data['value'])
                                ->orWhereHas('city', fn (Builder $cityQuery) =>
                                    $cityQuery->where('region_id', $data['value'])
                                )
                        );
                    }),
            ])
            ->recordActions([
                Action::make('activateUser')
                    ->label('Attiva')
                    ->icon(Heroicon::OutlinedCheckBadge)
                    ->color('success')
                    ->visible(fn (User $record): bool => blank($record->email_verified_at) && (auth()->user()?->can('gestire-utenti') ?? false))
                    ->requiresConfirmation()
                    ->modalHeading('Attivare questo utente?')
                    ->modalDescription('L\'utente verrà sbloccato manualmente anche senza email SMTP.')
                    ->action(function (User $record): void {
                        $record->forceFill([
                            'email_verified_at' => now(),
                        ])->save();

                        $record->memberProfile()?->update([
                            'is_active' => true,
                            'status' => MemberProfileStatus::Active,
                        ]);
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('attivaUtenti')
                        ->label('Attiva utenti')
                        ->icon(Heroicon::OutlinedCheckBadge)
                        ->color('success')
                        ->visible(fn () => auth()->user()?->can('gestire-utenti') ?? false)
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            foreach ($records as $record) {
                                if (filled($record->email_verified_at)) {
                                    continue;
                                }

                                $record->forceFill([
                                    'email_verified_at' => now(),
                                ])->save();

                                $record->memberProfile()?->update([
                                    'is_active' => true,
                                    'status' => MemberProfileStatus::Active,
                                ]);
                            }
                        }),
                    BulkAction::make('assegnaRuoloMassivo')
                        ->label('Assegna ruolo')
                        ->icon(Heroicon::OutlinedShieldCheck)
                        ->visible(fn () => auth()->user()?->can('assegnare-ruoli') ?? false)
                        ->form([
                            Select::make('role')
                                ->label('Ruolo')
                                ->options(fn () => Role::query()->orderBy('name')->pluck('name', 'name')->all())
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            foreach ($records as $record) {
                                $record->assignRole($data['role']);
                            }
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            UserPlanetsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
