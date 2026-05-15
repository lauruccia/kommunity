<?php

namespace App\Filament\Resources\Chapters\RelationManagers;

use App\Mail\ChapterInvitationMail;
use App\Models\ChapterInvitation;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

class ChapterInvitationsRelationManager extends RelationManager
{
    protected static string $relationship = 'invitations';

    protected static ?string $title = 'Inviti';

    protected static ?string $label = 'Invito';

    protected static ?string $pluralLabel = 'Inviti';

    // ── Autorizzazione ────────────────────────────────────────────────────────

    /**
     * La scheda Inviti è visibile solo ad admin e al leader del pianeta.
     */
    public function canViewAny(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasAnyRole(['super-admin', 'admin-community'])) {
            return true;
        }

        // Leader di questo specifico pianeta
        return $this->getOwnerRecord()->leader_id === $user->id
            || $user->can('gestire-inviti');
    }

    private function isAuthorized(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasAnyRole(['super-admin', 'admin-community'])) {
            return true;
        }

        return $this->getOwnerRecord()->leader_id === $user->id
            || $user->can('gestire-inviti');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('email')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('invitedBy.name')
                    ->label('Inviato da')
                    ->placeholder('-'),
                TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'  => 'warning',
                        'accepted' => 'success',
                        'expired'  => 'gray',
                        'revoked'  => 'danger',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending'  => 'In attesa',
                        'accepted' => 'Accettato',
                        'expired'  => 'Scaduto',
                        'revoked'  => 'Revocato',
                        default    => ucfirst($state),
                    }),
                TextColumn::make('expires_at')
                    ->label('Scade il')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Mai'),
                TextColumn::make('acceptedBy.name')
                    ->label('Accettato da')
                    ->placeholder('-'),
                TextColumn::make('accepted_at')
                    ->label('Accettato il')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label('Creato il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                Action::make('crea_invito')
                    ->label('Nuovo invito')
                    ->icon('heroicon-o-envelope')
                    ->visible(fn (): bool => $this->isAuthorized())
                    ->form([
                        TextInput::make('email')
                            ->label('Email destinatario')
                            ->email()
                            ->required(),
                        Textarea::make('message')
                            ->label('Messaggio personale (opzionale)')
                            ->rows(3)
                            ->placeholder('Scrivi un messaggio di benvenuto...'),
                        DateTimePicker::make('expires_at')
                            ->label('Scadenza (opzionale)')
                            ->helperText('Lascia vuoto per un invito senza scadenza.'),
                    ])
                    ->action(function (array $data): void {
                        /** @var \App\Models\Chapter $chapter */
                        $chapter = $this->getOwnerRecord();
                        $sender  = auth()->user();

                        // Controlla se esiste già un invito pendente per questa email
                        $existing = ChapterInvitation::query()
                            ->where('chapter_id', $chapter->id)
                            ->where('email', $data['email'])
                            ->where('status', 'pending')
                            ->first();

                        if ($existing) {
                            Notification::make()
                                ->title('Esiste già un invito attivo per questa email.')
                                ->warning()
                                ->send();
                            return;
                        }

                        $invitation = ChapterInvitation::create([
                            'chapter_id'          => $chapter->id,
                            'invited_by_user_id'  => $sender?->id,
                            'email'               => $data['email'],
                            'message'             => $data['message'] ?? null,
                            'status'              => 'pending',
                            'expires_at'          => $data['expires_at'] ?? null,
                        ]);

                        // Invia email
                        try {
                            Mail::to($data['email'])->send(new ChapterInvitationMail($invitation));

                            Notification::make()
                                ->title('Invito inviato a ' . $data['email'])
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Invito creato, ma l\'invio email è fallito: ' . $e->getMessage())
                                ->warning()
                                ->send();
                        }
                    }),
            ])
            ->recordActions([
                // Reinvia email
                Action::make('reinvia')
                    ->label('Reinvia email')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->visible(fn (ChapterInvitation $record): bool =>
                        $record->status === 'pending' && $this->isAuthorized()
                    )
                    ->action(function (ChapterInvitation $record): void {
                        try {
                            Mail::to($record->email)->send(new ChapterInvitationMail($record));
                            Notification::make()->title('Email reinviata.')->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Errore invio: ' . $e->getMessage())->danger()->send();
                        }
                    }),
                // Revoca
                Action::make('revoca')
                    ->label('Revoca')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Revocare questo invito?')
                    ->modalDescription('Il link diventerà inutilizzabile.')
                    ->visible(fn (ChapterInvitation $record): bool =>
                        $record->status === 'pending' && $this->isAuthorized()
                    )
                    ->action(function (ChapterInvitation $record): void {
                        $record->update(['status' => 'revoked']);
                        Notification::make()->title('Invito revocato.')->success()->send();
                    }),
            ])
            ->bulkActions([]);
    }
}
