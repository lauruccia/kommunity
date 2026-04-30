<?php

namespace App\Services;

use App\Enums\OneToOneStatus;
use App\Enums\ReferralStatus;
use App\Models\EventRegistration;
use App\Models\OneToOneRequest;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Servizio che calcola i KPI personali del membro per il widget analytics
 * della dashboard. Tutti i risultati sono cachati per 10 minuti.
 *
 * Gated dal feature flag `analytics_personal`. Il caller (DashboardController)
 * deve già verificare il flag prima di chiamarci.
 */
class MemberAnalyticsService
{
    public const CACHE_TTL_MINUTES = 10;

    /**
     * Calcola tutti i KPI per un utente.
     *
     * @return array{
     *   one_to_ones: array{ total: int, completed: int, this_month: int, last_30d: int },
     *   referrals: array{ sent: int, received: int, won: int, won_value: float, last_30d_sent: int },
     *   events: array{ attended: int, upcoming: int },
     *   subscription: array{ plan: ?string, monthly_price: ?float, since: ?string },
     *   roi: array{ value: float, formatted: string }|null,
     *   monthly_trend: array<int, array{ month: string, one_to_ones: int, referrals: int }>
     * }
     */
    public function calculate(User $user): array
    {
        return Cache::remember(
            "member_analytics_{$user->id}_v1",
            now()->addMinutes(self::CACHE_TTL_MINUTES),
            fn () => $this->compute($user)
        );
    }

    /**
     * Invalida la cache per un utente (da chiamare quando un 1:1/referral
     * cambia stato — gestito tramite observer in fase 2).
     */
    public function flush(User $user): void
    {
        Cache::forget("member_analytics_{$user->id}_v1");
    }

    // ─────────────────────────────────────────────────────────────────────────

    protected function compute(User $user): array
    {
        $oneToOneStats = $this->oneToOneStats($user);
        $referralStats = $this->referralStats($user);
        $eventStats    = $this->eventStats($user);
        $subscription  = $this->subscriptionStats($user);
        $roi           = $this->computeRoi($referralStats['won_value'], $subscription['monthly_price'] ?? null);
        $trend         = $this->monthlyTrend($user);

        return [
            'one_to_ones'   => $oneToOneStats,
            'referrals'     => $referralStats,
            'events'        => $eventStats,
            'subscription'  => $subscription,
            'roi'           => $roi,
            'monthly_trend' => $trend,
        ];
    }

    protected function oneToOneStats(User $user): array
    {
        $base = OneToOneRequest::query()
            ->where(fn ($q) => $q
                ->where('requester_id', $user->id)
                ->orWhere('recipient_id', $user->id));

        $total     = (clone $base)->count();
        $completed = (clone $base)->where('status', OneToOneStatus::Completed->value)->count();
        $thisMonth = (clone $base)
            ->where('status', OneToOneStatus::Completed->value)
            ->where('completed_at', '>=', now()->startOfMonth())
            ->count();
        $last30 = (clone $base)
            ->where('status', OneToOneStatus::Completed->value)
            ->where('completed_at', '>=', now()->subDays(30))
            ->count();

        return compact('total', 'completed', 'thisMonth', 'last30') + [
            'total'      => $total,
            'completed'  => $completed,
            'this_month' => $thisMonth,
            'last_30d'   => $last30,
        ];
    }

    protected function referralStats(User $user): array
    {
        $sent     = Referral::query()->where('sender_id', $user->id)->count();
        $received = Referral::query()->where('recipient_id', $user->id)->count();
        $won      = Referral::query()
            ->where(fn ($q) => $q
                ->where('sender_id', $user->id)
                ->orWhere('recipient_id', $user->id))
            ->where('status', ReferralStatus::Won->value)
            ->count();
        $wonValue = (float) Referral::query()
            ->where(fn ($q) => $q
                ->where('sender_id', $user->id)
                ->orWhere('recipient_id', $user->id))
            ->where('status', ReferralStatus::Won->value)
            ->sum('estimated_value');
        $last30Sent = Referral::query()
            ->where('sender_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return [
            'sent'          => $sent,
            'received'      => $received,
            'won'           => $won,
            'won_value'     => $wonValue,
            'last_30d_sent' => $last30Sent,
        ];
    }

    protected function eventStats(User $user): array
    {
        $attended = EventRegistration::query()
            ->where('user_id', $user->id)
            ->whereHas('event', fn ($q) => $q
                ->where('starts_at', '<', now())
                ->where('is_published', true))
            ->count();

        $upcoming = EventRegistration::query()
            ->where('user_id', $user->id)
            ->whereHas('event', fn ($q) => $q
                ->where('starts_at', '>=', now())
                ->where('is_published', true))
            ->count();

        return ['attended' => $attended, 'upcoming' => $upcoming];
    }

    protected function subscriptionStats(User $user): array
    {
        $sub = method_exists($user, 'activeSubscription') ? $user->activeSubscription() : null;
        if (! $sub) {
            return ['plan' => null, 'monthly_price' => null, 'since' => null];
        }

        $plan = $sub->plan ?? null;

        return [
            'plan'          => $plan?->name,
            'monthly_price' => $plan ? (float) $plan->price_monthly : null,
            'since'         => $sub->starts_at?->format('d/m/Y'),
        ];
    }

    protected function computeRoi(float $wonValue, ?float $monthlyPrice): ?array
    {
        if ($monthlyPrice === null || $monthlyPrice <= 0) {
            return null;
        }

        // ROI annualizzato: valore referral generato / (prezzo mensile * 12)
        $annualCost = $monthlyPrice * 12;
        $multiple   = $annualCost > 0 ? $wonValue / $annualCost : 0;

        return [
            'value'     => round($multiple, 2),
            'formatted' => number_format($multiple, 1, ',', '.') . 'x',
        ];
    }

    /**
     * 1:1 completati e referral chiusi negli ultimi 6 mesi, raggruppati.
     */
    protected function monthlyTrend(User $user): array
    {
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $cursor = now()->subMonths($i);
            $key    = $cursor->format('Y-m');
            $label  = $cursor->locale('it')->isoFormat('MMM YY');

            $oneToOnes = OneToOneRequest::query()
                ->where(fn ($q) => $q
                    ->where('requester_id', $user->id)
                    ->orWhere('recipient_id', $user->id))
                ->where('status', OneToOneStatus::Completed->value)
                ->whereYear('completed_at', $cursor->year)
                ->whereMonth('completed_at', $cursor->month)
                ->count();

            $referrals = Referral::query()
                ->where(fn ($q) => $q
                    ->where('sender_id', $user->id)
                    ->orWhere('recipient_id', $user->id))
                ->where('status', ReferralStatus::Won->value)
                ->whereYear('updated_at', $cursor->year)
                ->whereMonth('updated_at', $cursor->month)
                ->count();

            $months->push([
                'key'         => $key,
                'month'       => $label,
                'one_to_ones' => $oneToOnes,
                'referrals'   => $referrals,
            ]);
        }

        return $months->all();
    }
}
