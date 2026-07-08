<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\MemberProfile;
use App\Models\Profession;
use App\Models\Province;
use App\Models\Region;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DirectoryController extends Controller
{
    /**
     * Numero di minuti per cui mantenere stabile l'ordine random degli ID
     * (riduce il "table scan" di ORDER BY RAND() su MySQL e tiene la
     * paginazione coerente per l'utente).
     */
    private const RANDOM_SEED_TTL_MINUTES = 60;

    public function __invoke(Request $request): View
    {
        // ── Gate abbonamento ─────────────────────────────────────────────────
        // La directory è accessibile solo ai membri con abbonamento attivo.
        // Se nessun piano è configurato (piattaforma in fase di setup), si
        // lascia passare tutti per non bloccare il lancio operativo.
        $user = $request->user();
        $subscriptionPlansExist = \App\Models\SubscriptionPlan::query()->where('is_active', true)->exists();

        if ($subscriptionPlansExist && ! $user->hasDirectoryAccess()) {
            return view('directory.no-subscription');
        }

        $filters = $request->only([
            'search', 'profession', 'region', 'province', 'city', 'chapter',
        ]);

        $allowedPerPage = [40, 80, 120, 160];
        $perPage = in_array((int) $request->input('per_page'), $allowedPerPage)
            ? (int) $request->input('per_page')
            : 40;

        // Ordine random pre-calcolato e cachato per 60 minuti.
        // Su MySQL con > 5k profili, ORDER BY RAND() faceva full-table-scan
        // ad ogni richiesta; ora prendiamo gli ID una volta, li mescoliamo
        // a livello applicativo e li teniamo stabili per la paginazione.
        // Pianeta attivo dell'utente: la directory mostra solo i membri dello stesso Pianeta.
        // Usa chapter_members (non active_chapter_id) così anche chi ha più Pianeti
        // compare in tutti quelli di cui fa parte.
        $activePlanetId = $user->memberProfile?->active_chapter_id;

        // Cache separata per pianeta → ordine random stabile per ogni contesto.
        $cacheKey = 'directory.random_ids.planet.' . ($activePlanetId ?? 'all');

        $orderedIds = Cache::remember(
            $cacheKey,
            now()->addMinutes(self::RANDOM_SEED_TTL_MINUTES),
            fn () => MemberProfile::query()
                ->where('is_active', true)
                ->where('is_visible_in_directory', true)
                ->when($activePlanetId, fn ($q) =>
                    $q->whereExists(fn ($sub) => $sub
                        ->from('chapter_members')
                        ->whereColumn('chapter_members.user_id', 'member_profiles.user_id')
                        ->where('chapter_members.chapter_id', $activePlanetId)
                        ->where('chapter_members.status', 'active')
                    )
                )
                ->pluck('id')
                ->shuffle()
                ->all()
        );

        // FIELD(id, ...) ordina i record secondo la posizione nell'array di ID
        // (compatibile MySQL/MariaDB; per SQLite locale c'è il fallback sotto).
        $idsCsv     = implode(',', array_map('intval', $orderedIds));
        $hasOrderBy = $idsCsv !== '';

        $members = MemberProfile::query()
            ->join('users', 'users.id', '=', 'member_profiles.user_id')
            ->select('member_profiles.*')
            ->with(['user.memberOnepage', 'professions', 'categories', 'city.province.region', 'region', 'chapter'])
            ->withExists(['availabilitySlots as has_availability' => fn ($q) => $q->where('is_active', true)])
            ->where('is_active', true)
            ->where('is_visible_in_directory', true)
            // ── Scope Pianeta: mostra solo i membri del Pianeta attivo ────────
            ->when($activePlanetId, fn (Builder $q) =>
                $q->whereExists(fn ($sub) => $sub
                    ->from('chapter_members')
                    ->whereColumn('chapter_members.user_id', 'member_profiles.user_id')
                    ->where('chapter_members.chapter_id', $activePlanetId)
                    ->where('chapter_members.status', 'active')
                )
            )
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner
                        ->whereHas('user', fn (Builder $q) => $q->where('name', 'like', "%{$search}%"))
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('short_bio', 'like', "%{$search}%")
                        ->orWhere('bio', 'like', "%{$search}%")
                        ->orWhereHas('professions', fn (Builder $q) => $q->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('categories', fn (Builder $q) => $q->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['profession'] ?? null, fn (Builder $q, string $prof) =>
                $q->whereHas('professions', fn (Builder $inner) => $inner->where('professions.id', $prof))
            )
            ->when($filters['region'] ?? null, fn (Builder $q, string $region) =>
                $q->where('region_id', $region)
            )
            ->when($filters['province'] ?? null, fn (Builder $q, string $province) =>
                $q->whereHas('city', fn (Builder $inner) => $inner->where('province_id', $province))
            )
            ->when($filters['city'] ?? null, fn (Builder $q, string $city) =>
                $q->where('city_id', $city)
            )
            ->when($filters['chapter'] ?? null, fn (Builder $q, string $chapter) =>
                $q->where('active_chapter_id', $chapter)
            )
            ->when($hasOrderBy, function (Builder $q) use ($idsCsv, $orderedIds): void {
                // FIELD() è MySQL/MariaDB only; SQLite (locale) usa CASE WHEN
                $driver = config('database.connections.' . config('database.default') . '.driver');
                if ($driver === 'sqlite') {
                    $cases = collect($orderedIds)
                        ->map(fn ($id, $i) => 'WHEN member_profiles.id = ' . (int) $id . ' THEN ' . $i)
                        ->implode(' ');
                    $q->orderByRaw('CASE ' . $cases . ' ELSE 9999 END');
                } else {
                    // MySQL/MariaDB: ordine stabile basato sul seed random cachato
                    $q->orderByRaw('FIELD(member_profiles.id, ' . $idsCsv . ')');
                }
            }, function (Builder $q): void {
                // Fallback: nessun ID in cache (DB vuoto)
                $q->orderBy('member_profiles.id');
            })
            ->paginate($perPage)
            ->withQueryString();

        // Albero per filtro localita: Regione → Provincia → Città
        $regions = Region::query()
            ->with(['cities' => fn ($q) => $q->orderBy('name')])
            ->orderBy('name')
            ->get();

        // Aggiungiamo le province solo se la tabella esiste
        $provinces = collect();
        try {
            $provinces = Province::query()
                ->when($filters['region'] ?? null, fn ($q, $region) => $q->where('region_id', $region))
                ->with(['cities' => fn ($q) => $q->orderBy('name')])
                ->orderBy('name')
                ->get();
        } catch (\Throwable) {
            // Tabella province non ancora migrata
        }

        // Solo le professioni con almeno un membro visibile in directory
        // (stesso scope del Pianeta attivo applicato all'elenco membri).
        $professions = Profession::query()
            ->where('is_active', true)
            ->whereHas('memberProfiles', function (Builder $q) use ($activePlanetId): void {
                $q->where('member_profiles.is_active', true)
                    ->where('member_profiles.is_visible_in_directory', true)
                    ->when($activePlanetId, fn (Builder $inner) =>
                        $inner->whereExists(fn ($sub) => $sub
                            ->from('chapter_members')
                            ->whereColumn('chapter_members.user_id', 'member_profiles.user_id')
                            ->where('chapter_members.chapter_id', $activePlanetId)
                            ->where('chapter_members.status', 'active')
                        )
                    );
            })
            ->orderBy('name')
            ->get();

        return view('directory.index', [
            'members'        => $members,
            'professions'    => $professions,
            'regions'        => $regions,
            'provinces'      => $provinces,
            'chapters'       => Chapter::query()->where('is_active', true)->orderBy('name')->get(),
            'filters'        => $filters,
            'perPage'        => $perPage,
            'perPageOptions' => $allowedPerPage,
        ]);
    }
}
