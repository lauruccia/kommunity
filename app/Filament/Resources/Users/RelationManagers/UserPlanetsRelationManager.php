<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Models\Chapter;
use App\Models\MemberProfile;
use Filament\Actions\Action;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;

class UserPlanetsRelationManager extends RelationManager
{
    protected static string $relationship = 'planets';

    protected static ?string $title = 'Pianeti';

    protected static ?string $label = 'Pianeta';

    protected static ?string $pluralLabel = 'Pianeti';

    public function table(Table $table): Table
    {
        /** @var \App\Models\User $user */
        $user = $this->getOwnerRecord();
        $activePlanetId = $user->memberProfile?->active_chapter_id;

        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Pianeta')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('pivot.status')
                    ->label('Stato')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'   => 'success',
                        'inactive' => 'warning',
                        default    => 'gray',
                    }),
                TextColumn::make('pivot.joined_at')
                    ->label('Iscritto il')
                    ->dateTime('d/m/Y')
                    ->sortable(),
                IconColumn::make('is_active_planet')
                    ->label('Attivo ora')
                    ->state(fn (Chapter $record): bool => $record->id === $activePlanetId)
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-minus-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->headerActions([
                // ── Aggiungi a un Pianeta ────────────────────────────────────
                Action::make('aggiungi_pianeta')
                    ->label('Aggiungi a Pianeta')
                    ->icon('heroicon-o-plus-circle')
                    ->form([
                        Select::make('chapter_id')
                            ->label('Pianeta')
                            ->options(fn () => Chapter::where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Select::make('status')
                            ->label('Stato')
                            ->options(['active' => 'Attivo', 'inactive' => 'Inattivo'])
                            ->default('active')
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        /** @var \App\Models\User $user */
                        $user = $this->getOwnerRecord();

                        // Controlla che non sia già membro
                        $alreadyMember = \DB::table('chapter_members')
                            ->where('chapter_id', $data['chapter_id'])
                            ->where('user_id', $user->id)
                            ->exists();

                        if ($alreadyMember) {
                            Notification::make()
                                ->title('L\'utente è già membro di questo Pianeta.')
                                ->warning()
                                ->send();
                            return;
                        }

                        \DB::table('chapter_members')->insert([
                            'chapter_id' => $data['chapter_id'],
                            'user_id'    => $user->id,
                            'status'     => $data['status'],
                            'joined_at'  => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Utente aggiunto al Pianeta.')
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                // ── Imposta come Pianeta attivo ──────────────────────────────
                Action::make('imposta_attivo')
                    ->label('Imposta attivo')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (Chapter $record): void {
                        /** @var \App\Models\User $user */
                        $user = $this->getOwnerRecord();

                        MemberProfile::$adminOverrideLimit = true;
                        try {
                            $user->memberProfile?->update(['active_chapter_id' => $record->id]);
                        } finally {
                            MemberProfile::$adminOverrideLimit = false;
                        }

                        Notification::make()
                            ->title('"' . $record->name . '" impostato come Pianeta attivo.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Chapter $record): bool =>
                        $record->id !== $this->getOwnerRecord()->memberProfile?->active_chapter_id
                    ),

                // ── Rimuovi dal Pianeta ──────────────────────────────────────
                Action::make('rimuovi')
                    ->label('Rimuovi')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Rimuovere dal Pianeta?')
                    ->modalDescription('Il membro non sarà eliminato, ma perderà l\'accesso a questo Pianeta.')
                    ->action(function (Chapter $record): void {
                        /** @var \App\Models\User $user */
                        $user = $this->getOwnerRecord();

                        \DB::table('chapter_members')
                            ->where('chapter_id', $record->id)
                            ->where('user_id', $user->id)
                            ->delete();

                        // Se era il pianeta attivo, resetta a null
                        if ($user->memberProfile?->active_chapter_id === $record->id) {
                            MemberProfile::$adminOverrideLimit = true;
                            try {
                                $user->memberProfile?->update(['active_chapter_id' => null]);
                            } finally {
                                MemberProfile::$adminOverrideLimit = false;
                            }
                        }

                        Notification::make()
                            ->title('Utente rimosso dal Pianeta.')
                            ->success()
                            ->send();
                    })
                    // Non si può rimuovere se è l'unico pianeta
                    ->visible(fn (Chapter $record): bool =>
                        $this->getOwnerRecord()->planets()->count() > 1
                            || $this->getOwnerRecord()->memberProfile?->active_chapter_id !== $record->id
                    ),
            ])
            ->bulkActions([]);
    }
}
