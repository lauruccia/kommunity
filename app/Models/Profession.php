<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profession extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'is_active', 'parent_id', 'sort_order'];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // ── Gerarchia ────────────────────────────────────────────────────────────

    /** Professione padre (null se è radice) */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Profession::class, 'parent_id');
    }

    /** Sotto-professioni dirette */
    public function children(): HasMany
    {
        return $this->hasMany(Profession::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    /** Sotto-professioni attive */
    public function activeChildren(): HasMany
    {
        return $this->hasMany(Profession::class, 'parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    /** Solo le professioni radice (senza padre) */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /** Scope per professioni attive */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Restituisce l'etichetta con indentazione per dropdown gerarchici.
     * Es. "── Consulente marketing" se ha un padre.
     */
    public function getLabelAttribute(): string
    {
        $prefix = $this->parent_id ? '── ' : '';
        return $prefix . $this->name;
    }

    /**
     * Genera un elenco piatto ordinato gerarchicamente per Select dropdowns.
     * Radici → figli → nipoti, con prefissi di indentazione.
     */
    public static function flatTree(): \Illuminate\Support\Collection
    {
        $all = static::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->keyBy('id');

        $result = collect();

        $roots = $all->whereNull('parent_id')->values();

        foreach ($roots as $root) {
            $result->push(['id' => $root->id, 'label' => $root->name, 'depth' => 0]);
            static::flatTreeWalk($all, $root->id, 1, $result);
        }

        return $result;
    }

    private static function flatTreeWalk(
        \Illuminate\Support\Collection $all,
        int $parentId,
        int $depth,
        \Illuminate\Support\Collection &$result
    ): void {
        $prefix = str_repeat('── ', $depth);
        $children = $all->where('parent_id', $parentId)->values();

        foreach ($children as $child) {
            $result->push(['id' => $child->id, 'label' => $prefix . $child->name, 'depth' => $depth]);
            static::flatTreeWalk($all, $child->id, $depth + 1, $result);
        }
    }

    // ── Helper selezione multipla ─────────────────────────────────────────────

    /**
     * Espande un elenco di ID includendo tutti gli antenati gerarchici.
     * Es. selezionando "Sviluppatore software" viene incluso anche il padre
     * "Programmazione / Sviluppo software" (fino alla radice).
     *
     * @param  array<int|string>  $ids
     * @return array<int>
     */
    public static function expandWithAncestors(array $ids): array
    {
        return collect($ids)
            ->filter()
            ->map(fn ($v) => (int) $v)
            ->flatMap(function (int $id): array {
                $result = [$id];
                $prof = static::find($id);
                while ($prof?->parent_id) {
                    $result[] = (int) $prof->parent_id;
                    $prof = static::find($prof->parent_id);
                }
                return $result;
            })
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Inverso di expandWithAncestors(): rimuove dall'elenco gli ID che sono
     * antenati di altri ID selezionati (cioè i padri auto-inclusi al salvataggio).
     * Usato per pre-selezionare nei form solo le scelte effettive dell'utente,
     * così il limite max 3 non viene falsato dai padri gerarchici.
     *
     * @param  array<int|string>  $ids
     * @return array<int>
     */
    public static function stripAncestors(array $ids): array
    {
        $ids = collect($ids)->filter()->map(fn ($v) => (int) $v)->unique()->values();

        // I padri auto-inclusi coprono sempre l'intera catena, quindi basta
        // escludere gli ID che risultano parent_id di un altro ID selezionato.
        $parentIds = static::query()
            ->whereKey($ids->all())
            ->pluck('parent_id')
            ->filter()
            ->map(fn ($v) => (int) $v)
            ->all();

        return $ids->reject(fn (int $id) => in_array($id, $parentIds, true))->values()->all();
    }

    // ── Relazioni verso profili ───────────────────────────────────────────────

    /** Relazione legacy (singola professione) */
    public function memberProfilesSingle(): HasMany
    {
        return $this->hasMany(MemberProfile::class);
    }

    /** Nuova relazione M2M (professioni multiple per membro) */
    public function memberProfiles(): BelongsToMany
    {
        return $this->belongsToMany(MemberProfile::class, 'member_profile_profession');
    }
}
