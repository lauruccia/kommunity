<?php

namespace App\Filament\Resources\MembershipApplications;

use App\Filament\Resources\MembershipApplications\Pages\ListMembershipApplications;
use App\Mail\MembershipApprovedMail;
use App\Mail\MembershipRejectedMail;
use App\Models\Chapter;
use App\Models\MembershipApplication;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Candidature di ammissione a Kommunity (visitatori non registrati,
 * da card membro o homepage). L'admin approva — potendo cambiare il
 * Pianeta di destinazione — o rifiuta.
 *
 * All'approvazione:
 *   1. viene creato lo User (email già verificata, password casuale)
 *   2. UserObserver crea profilo + onepage
 *   3. il profilo riceve telefono/attività e il Pianeta attivo
 *   4. l'utente è iscritto al Pianeta (chapter_members)
 *   5. parte l'email di benvenuto con il link "imposta password"
 */
class MembershipApplicationResource extends Resource
{
    protected static ?string $model = MembershipApplication::class;
    protected static ?string $navigationLabel = 'Candidature ammissione';
    protected static ?string $modelLabel = 'candidatura';
    protected static ?string $pluralModelLabel = 'candidature di ammissione';
    protected static string|\UnitEnum|null $navigationGroup = 'Kommunity';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;
    protected static ?int $navigationSort = 4;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super-admin', 'admin-community']) ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    /** Badge nel menu con il conteggio delle candidature pending */
    public static function getNavigationBadge(): ?string
    {
        $count = MembershipApplication::query()->pending()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with(['chapter', 'presenter', 'reviewedBy', 'createdUser'])
                ->latest())
            ->columns([
                TextColumn::make('name')
                    ->label('Candidato')
                    ->weight('bold')
                    ->searchable()
                    ->description(fn (MembershipApplication $r): string => $r->email),

                TextColumn::make('applicant_type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (MembershipApplication $r): string => $r->isCompany() ? 'info' : 'gray')
                    ->formatStateUsing(fn (MembershipApplication $r): string => $r->isCompany() ? 'Azienda' : 'Privato'),

                TextColumn::make('vat_number')
                    ->label('P.IVA')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('profession')
                    ->label('Professione')
                    ->limit(30)
                    ->placeholder('—'),

                TextColumn::make('phone')
                    ->label('Telefono')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('source')
                    ->label('Da')
                    ->badge()
                    ->color(fn (MembershipApplication $r): string => $r->source === 'card' ? 'success' : 'gray')
                    ->formatStateUsing(fn (MembershipApplication $r): string => $r->sourceLabel()),

                TextColumn::make('presenter.name')
                    ->label('Presentato da')
                    ->placeholder(fn (MembershipApplication $r): string => $r->referrer_name ?: '—')
                    ->description(fn (MembershipApplication $r): string => $r->presenter ? 'membro' : ($r->referrer_name ? 'indicato nel form' : '')),

                TextColumn::make('chapter.name')
                    ->label('Pianeta proposto')
                    ->badge()
                    ->color('info')
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->color(fn (MembershipApplication $r): string => $r->statusColor())
                    ->formatStateUsing(fn (MembershipApplication $r): string => $r->statusLabel()),

                TextColumn::make('reviewedBy.name')
                    ->label('Revisionata da')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('reviewed_at')
                    ->label('Revisionata il')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Ricevuta il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Stato')
                    ->options([
                        'pending'  => 'In attesa',
                        'approved' => 'Approvata',
                        'rejected' => 'Rifiutata',
                    ])
                    ->default('pending'),

                SelectFilter::make('source')
                    ->label('Provenienza')
                    ->options([
                        'card' => 'Card membro',
                        'home' => 'Homepage',
                    ]),

                SelectFilter::make('chapter_id')
                    ->label('Pianeta')
                    ->options(fn () => Chapter::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable(),
            ])
            ->recordActions([
                Action::make('approva')
                    ->label('Approva')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (MembershipApplication $r) => $r->status === MembershipApplication::STATUS_PENDING)
                    ->modalHeading('Approvare la candidatura?')
                    ->modalDescription(fn (MembershipApplication $r): string => 'Verrà creato l\'account per ' . $r->name . ' (' . $r->email . '), iscritto al Pianeta scelto, e il nuovo membro riceverà l\'email di benvenuto con il link per impostare la password.')
                    ->form([
                        Select::make('chapter_id')
                            ->label('Pianeta di destinazione')
                            ->options(fn () => Chapter::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all())
                            ->default(fn (MembershipApplication $r) => $r->chapter_id)
                            ->searchable()
                            ->required()
                            ->helperText('Precompilato con il Pianeta proposto: puoi cambiarlo prima di approvare.'),
                    ])
                    ->action(function (MembershipApplication $record, array $data): void {
                        static::approve($record, (int) $data['chapter_id']);
                    }),

                Action::make('rifiuta')
                    ->label('Rifiuta')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (MembershipApplication $r) => $r->status === MembershipApplication::STATUS_PENDING)
                    ->modalHeading('Rifiutare la candidatura?')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Motivo (interno, opzionale)')
                            ->placeholder('Inserisci un motivo…'),
                        Toggle::make('send_email')
                            ->label('Invia email di cortesia al candidato')
                            ->default(true)
                            ->helperText('Un messaggio gentile che comunica che la candidatura non è stata accolta (il motivo NON viene incluso a meno che tu non attivi l\'opzione sotto).'),
                        Toggle::make('include_reason')
                            ->label('Includi il motivo nell\'email')
                            ->default(false),
                    ])
                    ->action(function (MembershipApplication $record, array $data): void {
                        $record->update([
                            'status'              => MembershipApplication::STATUS_REJECTED,
                            'rejection_reason'    => $data['rejection_reason'] ?? null,
                            'reviewed_by_user_id' => auth()->id(),
                            'reviewed_at'         => now(),
                        ]);

                        if (! empty($data['send_email'])) {
                            try {
                                $reason = ! empty($data['include_reason']) ? ($data['rejection_reason'] ?? null) : null;
                                Mail::to($record->email)
                                    ->send((new MembershipRejectedMail($record, $reason))
                                        ->locale($record->mailLocale()));
                            } catch (\Throwable $e) {
                                Log::warning('Email rifiuto candidatura non inviata: ' . $e->getMessage(), ['application_id' => $record->id]);
                            }
                        }

                        Notification::make()->title('Candidatura rifiutata.')->success()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Approva la candidatura: crea lo User, lo iscrive al Pianeta scelto
     * e invia l'email di benvenuto con il link "imposta password".
     */
    protected static function approve(MembershipApplication $record, int $chapterId): void
    {
        if ($record->status !== MembershipApplication::STATUS_PENDING) {
            Notification::make()->title('Candidatura già revisionata.')->warning()->send();

            return;
        }

        if (User::query()->where('email', $record->email)->exists()) {
            Notification::make()
                ->title('Esiste già un utente con questa email.')
                ->body('Impossibile creare l\'account: ' . $record->email)
                ->danger()
                ->send();

            return;
        }

        try {
            $user = DB::transaction(function () use ($record, $chapterId): User {
                // 1. Crea l'utente (password casuale: la imposterà via email)
                $user = User::create([
                    'name'               => $record->name,
                    'email'              => $record->email,
                    'password'           => Str::password(40),
                    'invited_by_user_id' => $record->presenter_user_id,
                    'invited_by_name'    => $record->presenter?->name ?? $record->referrer_name,
                    'locale'             => $record->mailLocale(),
                ]);

                // Email verificata: l'ammissione è passata dal vaglio admin e
                // il link "imposta password" viene spedito proprio a questa email.
                $user->forceFill(['email_verified_at' => now()])->saveQuietly();

                $user->assignRole(Role::findOrCreate('membro'));

                // 2. Profilo creato da UserObserver: completa i dati raccolti
                $profile = $user->memberProfile()->first();
                $profile?->updateWithAdminOverride([
                    'phone'             => $record->phone,
                    'profession_other'  => $record->profession,
                    'active_chapter_id' => $chapterId,
                ]);

                // 3. Iscrizione al Pianeta
                DB::table('chapter_members')->updateOrInsert(
                    ['chapter_id' => $chapterId, 'user_id' => $user->id],
                    ['status' => 'active', 'joined_at' => now(), 'updated_at' => now(), 'created_at' => now()]
                );

                // 4. Aggiorna la candidatura
                $record->update([
                    'status'              => MembershipApplication::STATUS_APPROVED,
                    'chapter_id'          => $chapterId,
                    'reviewed_by_user_id' => auth()->id(),
                    'reviewed_at'         => now(),
                    'created_user_id'     => $user->id,
                ]);

                return $user;
            });
        } catch (\Throwable $e) {
            Log::error('Approvazione candidatura fallita: ' . $e->getMessage(), ['application_id' => $record->id]);
            Notification::make()
                ->title('Errore durante l\'approvazione.')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return;
        }

        // 5. Email di benvenuto con link "imposta password" (fuori transazione)
        try {
            $token = Password::broker()->createToken($user);
            $setPasswordUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);

            Mail::to($user->email)
                ->send((new MembershipApprovedMail($record->fresh(['chapter']), $user, $setPasswordUrl))
                    ->locale($record->mailLocale()));

            Notification::make()
                ->title('Candidatura approvata.')
                ->body('Account creato e email di benvenuto inviata a ' . $user->email)
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Log::warning('Email benvenuto candidatura non inviata: ' . $e->getMessage(), ['application_id' => $record->id]);
            Notification::make()
                ->title('Account creato, ma email di benvenuto NON inviata.')
                ->body('Invita il membro a usare "Password dimenticata" con la sua email: ' . $user->email)
                ->warning()
                ->send();
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMembershipApplications::route('/'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }
}
