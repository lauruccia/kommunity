<?php

namespace App\Services;

use App\Enums\ReferralStatus;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Calcola il punteggio "valore generato" dei segnalatori della Kommunity.
 *
 * Logica premi: i punti vanno al SEGNALATORE (sender), cioè a chi porta lead
 * che si trasformano in consulenze reali e confermate dall'admin.
 *
 * Formula punteggio:
 *   punti = (n. referenze confermate × PUNTI_BASE) + (valore confermato totale / EURO_PER_PUNTO)
 *
 *   - PUNTI_BASE        = 50  → premia il volume di referenze andate a buon fine
 *   - EURO_PER_PUNTO    = 10  → 1 punto ogni 10€ di valore confermato (premia l'alto valore)
 *
 * Solo le referenze in stato "confirmed" (valore validato dall'admin) entrano nel conteggio.
 */
class ReferralScoreService
{
    public const PUNTI_BASE     = 50;
    public const EURO_PER_PUNTO = 10;

    /**
     * Classifica dei segnalatori per valore generato.
     *
     * @return Collection<int, array{user: User, confirmed_count: int, total_value: float, points: int}>
     */
    public function leaderboard(int $limit = 20, ?Carbon $from = null, ?Carbon $to = null): Collection
    {
        $rows = Referral::query()
            ->confirmed()
            ->when($from, fn ($q) => $q->where('approved_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('approved_at', '<=', $to))
            ->selectRaw('sender_id, COUNT(*) as confirmed_count, COALESCE(SUM(COALESCE(approved_value, declared_value, 0)), 0) as total_value')
            ->groupBy('sender_id')
            ->get();

        $users = User::query()
            ->with('memberProfile')
            ->whereIn('id', $rows->pluck('sender_id')->all())
            ->get()
            ->keyBy('id');

        return $rows
            ->map(function ($row) use ($users) {
                $value = (float) $row->total_value;

                return [
                    'user'            => $users->get($row->sender_id),
                    'confirmed_count' => (int) $row->confirmed_count,
                    'total_value'     => $value,
                    'points'          => $this->points((int) $row->confirmed_count, $value),
                ];
            })
            ->filter(fn ($r) => $r['user'] !== null)
            ->sortByDesc('points')
            ->values()
            ->take($limit);
    }

    /**
     * Punteggio di un singolo segnalatore.
     */
    public function points(int $confirmedCount, float $totalValue): int
    {
        return (int) round(($confirmedCount * self::PUNTI_BASE) + ($totalValue / self::EURO_PER_PUNTO));
    }

    /**
     * Riepilogo punteggio per un dato utente.
     *
     * @return array{confirmed_count: int, total_value: float, points: int}
     */
    public function summaryFor(int $userId): array
    {
        $row = Referral::query()
            ->confirmed()
            ->where('sender_id', $userId)
            ->selectRaw('COUNT(*) as confirmed_count, COALESCE(SUM(COALESCE(approved_value, declared_value, 0)), 0) as total_value')
            ->first();

        $count = (int) ($row->confirmed_count ?? 0);
        $value = (float) ($row->total_value ?? 0);

        return [
            'confirmed_count' => $count,
            'total_value'     => $value,
            'points'          => $this->points($count, $value),
        ];
    }
}
