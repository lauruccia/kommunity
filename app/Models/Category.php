<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['parent_id', 'name', 'slug', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** Categoria padre (null = categoria radice) */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /** Sottocategorie dirette */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('name');
    }

    /** Sottocategorie attive */
    public function activeChildren(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->where('is_active', true)->orderBy('name');
    }

    /** Membri che hanno selezionato questa categoria */
    public function memberProfiles(): BelongsToMany
    {
        return $this->belongsToMany(MemberProfile::class, 'member_profile_category');
    }

    /** Etichetta con gerarchia per i select (es. "Marketing > Social Media") */
    public function getFullNameAttribute(): string
    {
        return $this->parent ? $this->parent->name . ' › ' . $this->name : $this->name;
    }

    /** Scope: solo categorie radice (senza padre) */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    /** Scope: solo categorie figlie (con padre) */
    public function scopeChildren($query)
    {
        return $query->whereNotNull('parent_id');
    }
}
