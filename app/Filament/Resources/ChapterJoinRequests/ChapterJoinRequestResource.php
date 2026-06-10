<?php

namespace App\Filament\Resources\ChapterJoinRequests;

use App\Filament\Resources\ChapterJoinRequests\Pages\ListChapterJoinRequests;
use App\Models\Chapter;
use App\Models\ChapterJoinRequest;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ChapterJoinRequestResource extends Resource
{
    protected static ?string $model = ChapterJoinRequest::class;
    protected static ?string $navigationLabel = 'Richieste iscrizione';
    protected static ?string $modelLabel = 'richiesta';
    protected static ?string $pluralModelLabel = 'richieste iscrizione pianeti';
    protected static string|\UnitEnum|null $navigationGroup = 'Kommunity';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserPlus;
    protected static ?int $navigationSort = 5;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super-admin', 'admin-community']) ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    /** Badge rosso nel menu con il conteggio delle richieste pending */
    public static function getNavigationBadge(): ?string
    {
        $count = ChapterJoinRequest::where('status', 'pending')->count();
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
                ->with(['chapter', 'user.memberProfile.profession', 'invitedBy', 'reviewedBy'])
                ->latest())
            ->columns([
                TextColumn::make('user.name')
                    ->label('Membro')
                    ->weight('bold')
                    ->searchable()
                    ->description(fn (ChapterJoinRequest $r): string => $r->user?->email ?? ''),

                TextColumn::make('chapter.name')
                    ->label('Pianeta')
                    ->searchable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('user.memberProfile.profession.name')
                    ->label('Professione')
                    ->placeholder('-'),

                TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->color(fn (ChapterJoinRequest $record): string => $record->statusColor())
                    ->formatStateUsing(fn (ChapterJoinRequest $record): string => $record->statusLabel()),

                TextColumn::make('waitlist_position')
                    ->label('Pos. attesa')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('message')
                    ->label('Messaggio')
                    ->limit(60)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('invitedBy.name')
                    ->label('Invitato da')
                    ->placeholder('Spontanea')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('reviewedBy.name')
                    ->label('Revisionato da')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('reviewed_at')
                    ->label('Revisionata il')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Richiesta il')
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
                    ])
                    ->default('pending'),

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
                    ->visible(fn (ChapterJoinRequest $r) => in_array($r->status, ['pending', 'waitlist']))
                    ->requiresConfirmation()
                    ->modalHeading('Approvare la richiesta?')
                    ->action(function (ChapterJoinRequest $record): void {
                        $profile = $record->user?->memberProfile;
                        if (! $profile) {
                            Notification::make()->title('Profilo membro non trovato.')->danger()->send();
                            return;
                        }
                        $profile->update(['active_chapter_id' => $record->chapter_id]);
                        $record->update([
                            'status'              => 'accepted',
                            'reviewed_by_user_id' => auth()->id(),
                            'reviewed_at'         => now(),
                        ]);
                        DB::table('chapter_members')->updateOrInsert(
                            ['chapter_id' => $record->chapter_id, 'user_id' => $record->user_id],
                            ['status' => 'active', 'joined_at' => now(), 'updated_at' => now(), 'created_at' => now()]
                        );
                        Notification::make()->title('Richiesta approvata.')->success()->send();
                    }),

                Action::make('approva_override')
                    ->label('Approva (override)')
                    ->icon('heroicon-o-shield-check')
                    ->color('warning')
                    ->visible(fn (ChapterJoinRequest $r) => in_array($r->status, ['pending', 'waitlist']))
                    ->requiresConfirmation()
                    ->modalHeading('Approvare superando il limite professione?')
                    ->modalDescription('Supererai il limite massimo per questa categoria. Verrà registrato come override admin.')
                    ->action(function (ChapterJoinRequest $record): void {
                        $profile = $record->user?->memberProfile;
                        if (! $profile) {
                            Notification::make()->title('Profilo membro non trovato.')->danger()->send();
                            return;
                        }
                        $profile->forceFill(['active_chapter_id' => $record->chapter_id])->save();
                        $record->update([
                            'status'              => 'accepted',
                            'admin_override'      => true,
                            'reviewed_by_user_id' => auth()->id(),
                            'reviewed_at'         => now(),
                        ]);
                        DB::table('chapter_members')->updateOrInsert(
                            ['chapter_id' => $record->chapter_id, 'user_id' => $record->user_id],
                            ['status' => 'active', 'joined_at' => now(), 'updated_at' => now(), 'created_at' => now()]
                        );
                        Notification::make()->title('Approvato con override admin.')->warning()->send();
                    }),

                Action::make('rifiuta')
                    ->label('Rifiuta')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (ChapterJoinRequest $r) => in_array($r->status, ['pending', 'waitlist']))
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Motivo rifiuto (opzionale)')
                            ->placeholder('Inserisci un motivo...'),
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

                Action::make('sposta')
                    ->label('Sposta')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('info')
                    ->visible(fn (ChapterJoinRequest $r) => $r->status === 'pending')
                    ->form([
                        Select::make('new_chapter_id')
                            ->label('Nuovo Pianeta')
                            ->options(fn () => Chapter::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all())
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
                        ChapterJoinRequest::create([
                            'chapter_id' => $data['new_chapter_id'],
                            'user_id'    => $record->user_id,
                            'message'    => 'Spostato dal Pianeta precedente dall\'admin.',
                            'status'     => 'pending',
                        ]);
                        Notification::make()->title('Spostato a: ' . $newChapter?->name)->success()->send();
                    }),
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
            'index' => ListChapterJoinRequests::route('/'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }
}
