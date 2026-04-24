<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profession extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

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
