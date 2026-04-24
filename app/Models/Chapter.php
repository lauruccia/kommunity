<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Chapter extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'city_id',
        'leader_id',
        'cover_image',
        'max_members_per_profession',
        'is_active',
        'is_invite_only',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_invite_only' => 'boolean',
            'max_members_per_profession' => 'integer',
        ];
    }

    /**
     * Conta i posti disponibili per una specifica professione in questo Pianeta.
     * Ritorna null se non c'è limite (max_members_per_profession = 0).
     */
    public function availableSlotsForProfession(int $professionId): int
    {
        $assigned = MemberProfile::query()
            ->where('chapter_id', $this->id)
            ->where('profession_id', $professionId)
            ->count();

        return max(0, $this->max_members_per_profession - $assigned);
    }

    /**
     * Prossima posizione in lista d'attesa per questo Pianeta e professione.
     */
    public function nextWaitlistPosition(int $professionId): int
    {
        $max = \DB::table('chapter_join_requests')
            ->where('chapter_id', $this->id)
            ->whereNotNull('waitlist_position')
            ->max('waitlist_position');

        return ($max ?? 0) + 1;
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chapter_members')
            ->withPivot(['status', 'joined_at'])
            ->withTimestamps();
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function memberProfiles(): HasMany
    {
        return $this->hasMany(MemberProfile::class);
    }

    /** Regioni geograficamente coperte da questo Pianeta (molti-a-molti). */
    public function coveredRegions(): BelongsToMany
    {
        return $this->belongsToMany(Region::class, 'chapter_region_coverage')
            ->withTimestamps();
    }

    /** Richieste di iscrizione ricevute. */
    public function joinRequests(): HasMany
    {
        return $this->hasMany(ChapterJoinRequest::class);
    }

    /** Inviti diretti emessi per questo Pianeta. */
    public function invitations(): HasMany
    {
        return $this->hasMany(ChapterInvitation::class);
    }

    public function professionDistributionSummary(): string
    {
        $rows = $this->memberProfiles()
            ->select('professions.name', DB::raw('count(*) as total'))
            ->join('professions', 'professions.id', '=', 'member_profiles.profession_id')
            ->groupBy('professions.name')
            ->orderByDesc('total')
            ->orderBy('professions.name')
            ->get();

        if ($rows->isEmpty()) {
            return 'Nessun professionista assegnato.';
        }

        return $rows
            ->map(fn ($row) => $row->name.': '.$row->total.'/'.$this->max_members_per_profession)
            ->implode("\n");
    }
}
