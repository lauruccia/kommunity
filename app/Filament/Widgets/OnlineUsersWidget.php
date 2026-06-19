<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

/**
 * Widget Filament: utenti attivi negli ultimi 5 minuti e cosa stanno facendo.
 *
 * Auto-refresh ogni 30 secondi. Visibile solo a super-admin e admin-community.
 */
class OnlineUsersWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 0;

    protected static ?string $pollingInterval = '30s';

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['super-admin', 'admin-community']) ?? false;
    }

    public function getHeading(): string
    {
        $count = User::where('last_seen_at', '>=', now()->subMinutes(5))->count();

        return $count > 0
            ? "Utenti online ora · {$count} " . ($count === 1 ? 'attivo' : 'attivi')
            : 'Utenti online ora · nessuno';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->whereNotNull('last_seen_at')
                    ->where('last_seen_at', '>=', now()->subMinutes(5))
                    ->latest('last_seen_at')
            )
            ->emptyStateHeading('Nessun utente online in questo momento')
            ->emptyStateDescription('Gli utenti attivi negli ultimi 5 minuti appariranno qui automaticamente.')
            ->emptyStateIcon('heroicon-o-user-group')
            ->columns([
                TextColumn::make('name')
                    ->label('Utente')
                    ->description(fn (User $r): string => $r->email)
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('roles.name')
                    ->label('Ruolo')
                    ->badge()
                    ->separator(', ')
                    ->color(fn (string $state): string => match ($state) {
                        'super-admin'       => 'danger',
                        'admin-community'   => 'warning',
                        'leader-capitolo'   => 'info',
                        default             => 'gray',
                    }),

                TextColumn::make('last_seen_route')
                    ->label('Sta visitando')
                    ->state(fn (User $r): string => self::activityLabel(
                        $r->last_seen_route ?? '',
                        $r->last_seen_url   ?? '',
                    ))
                    ->icon('heroicon-o-cursor-arrow-rays')
                    ->iconColor('primary')
                    ->wrap(),

                TextColumn::make('last_seen_at')
                    ->label('Visto')
                    ->since()
                    ->tooltip(fn (User $r): string => $r->last_seen_at?->format('d/m/Y H:i:s') ?? '')
                    ->color('success'),
            ])
            ->recordActions([
                Action::make('open_user')
                    ->label('Apri scheda')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (User $r): string => '/admin/users/' . $r->getKey() . '/edit'),
            ]);
    }

    /**
     * Converte il nome della route (o l'URL grezzo) in un'etichetta leggibile.
     */
    private static function activityLabel(string $route, string $url): string
    {
        return match (true) {
            str_starts_with($route, 'filament.admin.resources.users')          => '🛡️ Gestione utenti (admin)',
            str_starts_with($route, 'filament.admin.resources.events')         => '🛡️ Gestione eventi (admin)',
            str_starts_with($route, 'filament.admin.resources.forum')          => '🛡️ Gestione forum (admin)',
            str_starts_with($route, 'filament.admin.resources.chapters')       => '🛡️ Gestione capitoli (admin)',
            str_starts_with($route, 'filament.admin.resources.subscription')   => '🛡️ Abbonamenti (admin)',
            str_starts_with($route, 'filament.admin')                          => '🛡️ Pannello admin',
            $route === 'dashboard'                                              => '🏠 Dashboard',
            $route === 'home'                                                   => '🏡 Home',
            $route === 'directory.index'                                        => '🔍 Directory utenti',
            $route === 'members.show'                                           => '👤 Profilo utente',
            $route === 'events.index'                                           => '📅 Lista eventi',
            $route === 'events.show'                                            => '📅 Dettaglio evento',
            $route === 'forum.index'                                            => '💬 Forum',
            $route === 'forum.show'                                             => '💬 Thread forum',
            $route === 'conversations.index'                                    => '✉️ Lista messaggi',
            $route === 'conversations.show'                                     => '✉️ Conversazione',
            str_starts_with($route, 'one-to-ones.')                            => '🤝 One-to-one',
            str_starts_with($route, 'profile.')                                => '⚙️ Modifica profilo',
            str_starts_with($route, 'referrals.')                              => '⭐ Referenze',
            str_starts_with($route, 'subscriptions.')                          => '💳 Abbonamento',
            str_starts_with($route, 'notifications.')                          => '🔔 Notifiche',
            $route === 'admin.cache.index'                                      => '🗑️ Cache (admin)',
            $url !== ''                                                         => '🌐 ' . $url,
            default                                                             => '—',
        };
    }
}
