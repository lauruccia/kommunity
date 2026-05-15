<?php

namespace App\Filament\Resources\Chapters\RelationManagers;

use App\Enums\MemberProfileStatus;
use App\Models\MemberProfile;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MemberProfilesRelationManager extends RelationManager
{
    /**
     * Usa allMemberProfiles() — hasManyThrough via chapter_members.
     * Mostra TUTTI i membri iscritti al pianeta, non solo quelli con active_chapter_id = questo.
     */
    protected static string $relationship = 'allMemberProfiles';

    protected static ?string $title = 'Membri del Pianeta';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->defaultSort('id', 'asc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Membro')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('profession.name')
                    ->label('Professione')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('profession_other')
                    ->label('Professione (altro)')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('categories.name')
                    ->label('Categorie')
                    ->badge()
                    ->separator(', ')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('city.name')
                    ->label('Città')
                    ->placeholder('-'),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Stato profilo')
                    ->badge()
                    ->formatStateUsing(fn (MemberProfileStatus|string|null $state): string => $state instanceof MemberProfileStatus ? $state->label() : match ($state) {
                        'active'           => 'Attivo',
                        'pending_approval' => 'In approvazione',
                        'draft'            => 'Bozza',
                        'suspended'        => 'Sospeso',
                        default            => $state ?: '-',
                    })
                    ->color(fn (MemberProfileStatus|string|null $state): string => match ($state instanceof MemberProfileStatus ? $state->value : $state) {
                        'active'           => 'success',
                        'pending_approval' => 'warning',
                        'suspended'        => 'danger',
                        default            => 'gray',
                    }),
                IconColumn::make('is_active')
                    ->label('Attivo')
                    ->boolean(),
            ])
            ->filters([])
            ->headerActions([
                Action::make('aggiungi_membro')
                    ->label('Aggiungi membro')
                    ->icon('heroicon-o-user-plus')
                    ->modalHeading('Assegna membro al Pianeta')
                    ->form([
                        Select::make('user_id')
                            ->label('Membro')
                            ->options(function () {
                                // Mostra tutti gli utenti che hanno un profilo
                                // ed esclude chi è già membro di questo pianeta
                                $chapterId = $this->getOwnerRecord()->id;
                                $alreadyIn = \DB::table('chapter_members')
                                    ->where('chapter_id', $chapterId)
                                    ->pluck('user_id');

                                return User::query()
                                    ->whereHas('memberProfile')
                                    ->whereNotIn('id', $alreadyIn)
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(fn (User $u) => [$u->id => $u->name . ' (' . $u->email . ')']);
                            })
                            ->searchable()
                            ->required(),
                        Select::make('status')
                            ->label('Stato iscrizione')
                            ->options(['active' => 'Attivo', 'inactive' => 'Inattivo'])
                            ->default('active')
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $chapter = $this->getOwnerRecord();

                        \DB::table('chapter_members')->insertOrIgnore([
                            'chapter_id' => $chapter->id,
                            'user_id'    => $data['user_id'],
                            'status'     => $data['status'],
                            'joined_at'  => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Membro aggiunto al Pianeta.')
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                Action::make('imposta_principale')
                    ->label('Imposta principale')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->tooltip('Imposta questo come Pianeta principale del membro')
                    ->visible(fn (MemberProfile $record): bool =>
                        $record->active_chapter_id !== $this->getOwnerRecord()->id
                    )
                    ->action(function (MemberProfile $record): void {
                        \App\Models\MemberProfile::$adminOverrideLimit = true;
                        try {
                            $record->update(['active_chapter_id' => $this->getOwnerRecord()->id]);
                        } finally {
                            \App\Models\MemberProfile::$adminOverrideLimit = false;
                        }
                        Notification::make()->title('Pianeta principale aggiornato.')->success()->send();
                    }),

                Action::make('rimuovi')
                    ->label('Rimuovi')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Rimuovere il membro dal Pianeta?')
                    ->modalDescription('Il membro non sarà eliminato, ma perderà l\'accesso a questo Pianeta.')
                    ->action(function (MemberProfile $record): void {
                        $chapter = $this->getOwnerRecord();

                        \DB::table('chapter_members')
                            ->where('chapter_id', $chapter->id)
                            ->where('user_id', $record->user_id)
                            ->delete();

                        // Se era il pianeta attivo, resetta a null
                        if ($record->active_chapter_id === $chapter->id) {
                            \App\Models\MemberProfile::$adminOverrideLimit = true;
                            try {
                                $record->update(['active_chapter_id' => null]);
                            } finally {
                                \App\Models\MemberProfile::$adminOverrideLimit = false;
                            }
                        }

                        Notification::make()->title('Membro rimosso dal Pianeta.')->success()->send();
                    }),
            ])
            ->bulkActions([]);
    }
}
