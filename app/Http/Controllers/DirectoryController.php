<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Chapter;
use App\Models\MemberProfile;
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
        $filters = $request->only([
            'search', 'category', 'region', 'province', 'city', 'chapter',
        ]);

        // Ordine random pre-calcolato e cachato per 60 minuti.
        // Su MySQL con > 5k profili, ORDER BY RAND() faceva full-table-scan
        // ad ogni richiesta; ora prendiamo gli ID una volta, li mescoliamo
        // a livello applicativo e li teniamo stabili per la paginazione.
        $orderedIds = Cache::remember(
            'directory.random_ids.v1',
            now()->addMinutes(self::RANDOM_SEED_TTL_MINUTES),
            fn () => MemberProfile::query()
                ->where('is_active', true)
                ->where('is_visible_in_directory', true)
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
            ->when($filters['category'] ?? null, fn (Builder $q, string $cat) =>
                $q->whereHas('categories', fn (Builder $inner) => $inner->where('categories.id', $cat)
                    ->orWhere('categories.parent_id', $cat))
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
                $q->where('chapter_id', $chapter)
            )
            ->when($hasOrderBy, function (Builder $q) use ($idsCsv): void {
                // Ordine stabile basato sul seed random cachato (no table scan)
                $q->orderByRaw('FIELD(member_profiles.id, ' . $idsCsv . ')');
            }, function (Builder $q): void {
                // Fallback: nessun ID in cache (DB vuoto)
                $q->orderBy('member_profiles.id');
            })
            ->paginate(12)
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

        // Categorie ad albero per la sidebar
        $rootCategories = Category::query()
            ->with(['activeChildren'])
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('directory.index', [
            'members'        => $members,
            'rootCategories' => $rootCategories,
            'regions'        => $regions,
            'provinces'      => $provinces,
            'chapters'       => Chapter::query()->where('is_active', true)->orderBy('name')->get(),
            'filters'        => $filters,
        ]);
    }
}
