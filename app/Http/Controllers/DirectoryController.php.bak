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

class DirectoryController extends Controller
{
    public function __invoke(Request $request): View
    {
        $filters = $request->only([
            'search', 'category', 'region', 'province', 'city', 'chapter',
        ]);

        $members = MemberProfile::query()
            ->join('users', 'users.id', '=', 'member_profiles.user_id')
            ->select('member_profiles.*')
            ->with(['user.memberOnepage', 'professions', 'categories', 'city.province.region', 'region', 'chapter'])
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
            ->inRandomOrder()
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
