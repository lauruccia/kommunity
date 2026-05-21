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
