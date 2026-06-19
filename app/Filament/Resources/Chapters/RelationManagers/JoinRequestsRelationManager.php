<?php

namespace App\Filament\Resources\Chapters\RelationManagers;

use App\Models\Chapter;
use App\Models\ChapterJoinRequest;
use App\Models\MemberProfile;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class JoinRequestsRelationManager extends RelationManager
{
    protected static string $relationship = 'joinRequests';
    protected static ?string $title = 'Richieste di iscrizione';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Utente')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('user.memberProfile.profession.name')
                    ->label('Professione')
                    ->placeholder('-'),
                TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->color(fn (ChapterJoinRequest $record) => $record->statusColor())
                    ->formatStateUsing(fn (ChapterJoinRequest $record) => $record->statusLabel()),
                TextColumn::make('waitlist_position')
                    ->label('Pos. lista attesa')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('admin_override')
                    ->label('Override')
                    ->badge()
                    ->color(fn ($state) => $state ? 'warning' : 'gray')
                    ->formatStateUsing(fn ($state) => $state ? 'Sì (override admin)' : 'No'),
                TextColumn::make('invitedBy.name')
                    ->label('Invitato da')
                    ->placeholder('Richiesta spontanea'),
                TextColumn::make('message')
                    ->label('Messaggio')
                    ->limit(60)
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label('Data richiesta')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Stato')
                    ->options([
                        'pending'  => 'In attesa',
                        'accepted' => 'Accettata',
                        'rejected' => 'Rifiutata',
                        'waitlist' => 'Lista d\'attesa',
                        'moved'    => 'Spostato',
                    ]),
            ])
            ->recordActions([
                // ── Approva (rispetta limite professione) ────────────────────
                Action::make('approva')
                    ->label('Approva')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (ChapterJoinRequest $record) => in_array($record->status, ['pending', 'waitlist']))
                    ->requiresConfirmation()
                    ->modalHeading('Approvare la richiesta?')
                    ->modalDescription('L\'utente verrà assegnato a questo Pianeta. Se il limite professione è raggiunto, usa "Approva con override".')
                    ->action(function (ChapterJoinRequest $record): void {
                        $profile = $record->user?->memberProfile;

                        if (! $profile) {
                            Notification::make()
                                ->title('Errore: profilo utente non trovato.')
                                ->danger()
                                ->send();
                            return;
                        }

                        try {
                            $profile->update(['active_chapter_id' => $record->chapter_id]);

                            $record->update([
                                'status'              => 'accepted',
                                'reviewed_by_user_id' => auth()->id(),
                                'reviewed_at'         => now(),
                                'admin_override'      => false,
                            ]);

                            \DB::table('chapter_members')->updateOrInsert(
                                ['chapter_id' => $record->chapter_id, 'user_id' => $record->user_id],
                                ['status' => 'active', 'joined_at' => now(), 'updated_at' => now(), 'created_at' => now()]
                            );

                            Notification::make()
                                ->title('Richiesta approvata — utente assegnato al Pianeta.')
                                ->success()
                                ->send();
                        } catch (\Illuminate\Validation\ValidationException $e) {
                            Notification::make()
                                ->title('Limite raggiunto: usa "Approva con override" per forzare l\'assegnazione.')
                                ->warning()
                                ->send();
                        }
                    }),

                // ── Approva con override (supera il limite professione) ───────
                Action::make('approva_override')
                    ->label('Approva con override')
                    ->icon('heroicon-o-shield-check')
                    ->color('warning')
                    ->visible(fn (ChapterJoinRequest $record) => in_array($record->status, ['pending', 'waitlist']))
                    ->requiresConfirmation()
                    ->modalHeading('Approvare superando il limite?')
                    ->modalDescription('Questo supererà il limite massimo di professionisti per questa categoria. L\'azione verrà registrata come override admin.')
                    ->action(function (ChapterJoinRequest $record): void {
                        $profile = $record->user?->memberProfile;

                        if (! $profile) {
                            Notification::make()->title('Errore: profilo non trovato.')->danger()->send();
                            return;
                        }

                        $profile->updateWithAdminOverride(['active_chapter_id' => $record->chapter_id]);

                        $record->update([
                            'status'              => 'accepted',
                            'admin_override'      => true,
                            'reviewed_by_user_id' => auth()->id(),
                            'reviewed_at'         => now(),
                        ]);

                        \DB::table('chapter_members')->updateOrInsert(
                            ['chapter_id' => $record->chapter_id, 'user_id' => $record->user_id],
                            ['status' => 'active', 'joined_at' => now(), 'updated_at' => now(), 'created_at' => now()]
                        );

                        Notification::make()
                            ->title('Richiesta approvata con override admin.')
                            ->warning()
                            ->send();
                    }),

                // ── Rifiuta ──────────────────────────────────────────────────
                Action::make('rifiuta')
                    ->label('Rifiuta')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (ChapterJoinRequest $record) => in_array($record->status, ['pending', 'waitlist']))
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Motivo del rifiuto (opzionale)')
                            ->placeholder('Inserisci un motivo da comunicare all\'utente...'),
                    ])
                    ->action(function (ChapterJoinRequest $record, array $data): void {
                        $record->update([
                            'status'              => 'rejected',
                            'rejection_reason'    => $data['rejection_reason'] ?? null,
                            'reviewed_by_user_id' => auth()->id(),
                            'reviewed_at'         => now(),
                        ]);

                        Notification::make()->title('Richiesta rifiutata.')->success()->send();
                    }),

                // ── Sposta su altro Pianeta ──────────────────────────────────
                Action::make('sposta')
                    ->label('Sposta su altro Pianeta')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('info')
                    ->visible(fn (ChapterJoinRequest $record) => $record->status === 'pending')
                    ->form([
                        Select::make('new_chapter_id')
                            ->label('Nuovo Pianeta')
                            ->options(fn () => Chapter::query()->where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (ChapterJoinRequest $record, array $data): void {
                        $newChapter = Chapter::find($data['new_chapter_id']);

                        $record->update([
                            'chapter_id'          => $data['new_chapter_id'],
                            'status'              => 'moved',
                            'reviewed_by_user_id' => auth()->id(),
                            'reviewed_at'         => now(),
                            'rejection_reason'    => 'Spostato al Pianeta: ' . $newChapter?->name,
                        ]);

                        // Crea nuova richiesta pending nel nuovo Pianeta
                        ChapterJoinRequest::create([
                            'chapter_id' => $data['new_chapter_id'],
                            'user_id'    => $record->user_id,
                            'message'    => 'Spostato dal Pianeta precedente dall\'admin.',
                            'status'     => 'pending',
                        ]);

                        Notification::make()
                            ->title('Utente spostato al Pianeta: ' . $newChapter?->name)
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([]);
    }
}
