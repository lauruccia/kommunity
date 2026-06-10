<?php

namespace App\Filament\Widgets;

use App\Enums\SubscriptionStatus;
use App\Models\ChapterJoinRequest;
use App\Models\Event;
use App\Models\ForumThread;
use App\Models\MemberSubscription;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Widget dashboard: KPI principali della piattaforma.
 * Visibile solo a super-admin e admin-community.
 */
class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = -1;

    protected ?string $pollingInterval = '60s';

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['super-admin', 'admin-community']) ?? false;
    }

    protected function getStats(): array
    {
        // Nuovi iscritti
        $newToday  = User::whereDate('created_at', today())->count();
        $newWeek   = User::where('created_at', '>=', now()->subDays(7))->count();
        $newMonth  = User::where('created_at', '>=', now()->subDays(30))->count();
        $totalUsers = User::count();

        // Abbonamenti attivi
        $activeSubscriptions = MemberSubscription::where('status', SubscriptionStatus::Active)->count();
        $pendingSubscriptions = MemberSubscription::where('status', SubscriptionStatus::Pending)->count();

        // Richieste iscrizione pianeti pendenti
        $pendingJoinRequests = ChapterJoinRequest::where('status', 'pending')->count();
        $waitlistRequests    = ChapterJoinRequest::where('status', 'waitlist')->count();

        // Eventi imminenti (prossimi 7 giorni)
        $upcomingEvents = Event::where('starts_at', '>=', now())
            ->where('starts_at', '<=', now()->addDays(7))
            ->count();
        $totalEvents = Event::where('starts_at', '>=', now())->count();

        // Forum
        $threadCount   = ForumThread::count();
        $newThreadsWeek = ForumThread::where('created_at', '>=', now()->subDays(7))->count();

        // Utenti non verificati (da attivare)
        $unverified = User::whereNull('email_verified_at')->count();

        return [
            Stat::make('Utenti totali', $totalUsers)
                ->description("+{$newToday} oggi · +{$newWeek} questa settimana")
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('primary')
                ->chart(
                    collect(range(6, 0))->map(fn ($d) =>
                        User::whereDate('created_at', today()->subDays($d))->count()
                    )->toArray()
                ),

            Stat::make('Nuovi questo mese', $newMonth)
                ->description("Da attivare: {$unverified}")
                ->descriptionIcon($unverified > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($unverified > 0 ? 'warning' : 'success'),

            Stat::make('Abbonamenti attivi', $activeSubscriptions)
                ->description($pendingSubscriptions > 0 ? "{$pendingSubscriptions} in attesa di approvazione" : 'Nessuno in attesa')
                ->descriptionIcon($pendingSubscriptions > 0 ? 'heroicon-m-clock' : 'heroicon-m-check-circle')
                ->color($pendingSubscriptions > 0 ? 'warning' : 'success')
                ->url('/admin/member-subscriptions'),

            Stat::make('Richieste pianeti', $pendingJoinRequests)
                ->description($waitlistRequests > 0 ? "{$waitlistRequests} in lista d'attesa" : 'Nessuno in attesa')
                ->descriptionIcon($pendingJoinRequests > 0 ? 'heroicon-m-user-group' : 'heroicon-m-check-circle')
                ->color($pendingJoinRequests > 0 ? 'warning' : 'success')
                ->url('/admin/chapter-join-requests'),

            Stat::make('Eventi in arrivo', $upcomingEvents)
                ->description("Totale futuri: {$totalEvents}")
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            Stat::make('Thread forum', $threadCount)
                ->description("+{$newThreadsWeek} questa settimana")
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('gray'),
        ];
    }
}
