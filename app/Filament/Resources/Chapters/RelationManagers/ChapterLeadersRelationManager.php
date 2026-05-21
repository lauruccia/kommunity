<?php

namespace App\Filament\Resources\Chapters\RelationManagers;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class ChapterLeadersRelationManager extends RelationManager
{
    protected static string $relationship = 'leaders';

    protected static ?string $title = 'Leader';

    protected static ?string $label = 'Leader';

    protected static ?string $pluralLabel = 'Leader';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
            ])
            ->headerActions([
                Action::make('aggiungi_leader')
                    ->label('Aggiungi leader')
                    ->icon('heroicon-o-user-plus')
                    ->modalHeading('Aggiungi leader al Pianeta')
                    ->form([
                        Select::make('user_id')
                            ->label('Membro')
                            ->options(function () {
                                $chapterId = $this->getOwnerRecord()->id;

                                // Esclude chi è già leader di questo pianeta
                                $alreadyLeader = DB::table('chapter_leaders')
                                    ->where('chapter_id', $chapterId)
                                    ->pluck('user_id');

                                return User::query()
                                    ->whereNotIn('id', $alreadyLeader)
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(fn (User $u) => [
                                        $u->id => $u->name . ' (' . $u->email . ')',
                                    ]);
                            })
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $chapterId = $this->getOwnerRecord()->id;
                        $userId    = $data['user_id'];

                        // Aggiunge come leader
                        DB::table('chapter_leaders')->insertOrIgnore([
                            'chapter_id' => $chapterId,
                            'user_id'    => $userId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        // Aggiunge automaticamente come membro attivo del Pianeta
                        DB::table('chapter_members')->updateOrInsert(
                            ['chapter_id' => $chapterId, 'user_id' => $userId],
                            [
                                'status'     => 'active',
                                'joined_at'  => now(),
                                'updated_at' => now(),
                                'created_at' => now(),
                            ]
                        );

                        // Imposta il pianeta come attivo sul profilo, se non ne ha uno
                        $profile = \App\Models\MemberProfile::query()->where('user_id', $userId)->first();
                        if ($profile && ! $profile->active_chapter_id) {
                            \App\Models\MemberProfile::$adminOverrideLimit = true;
                            try {
                                $profile->update(['active_chapter_id' => $chapterId]);
                            } finally {
                                \App\Models\MemberProfile::$adminOverrideLimit = false;
                            }
                        }

                        Notification::make()
                            ->title('Leader aggiunto al Pianeta e iscritto come membro.')
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                Action::make('rimuovi_leader')
                    ->label('Rimuovi')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Rimuovere il leader?')
                    ->modalDescription('Il membro non verrà eliminato, perderà solo il ruolo di leader in questo Pianeta.')
                    ->action(function (User $record): void {
                        DB::table('chapter_leaders')
                            ->where('chapter_id', $this->getOwnerRecord()->id)
                            ->where('user_id', $record->id)
                            ->delete();

                        Notification::make()
                            ->title('Leader rimosso dal Pianeta.')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([]);
    }
}
