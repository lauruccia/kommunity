<?php

namespace App\Models;

use App\Enums\EventAttendanceStatus;
use App\Enums\EventType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'chapter_id',
        'organizer_id',
        'title',
        'slug',
        'description',
        'cover_image',
        'type',
        'starts_at',
        'ends_at',
        'location',
        'meeting_url',
        'capacity',
        'status',
        'is_published',
        // ── Audience: chi può vedere e partecipare ───────────────────────────
        // 'all'                      → tutti i membri
        // 'by_planet'                → solo Pianeti selezionati
        // 'by_profession'            → solo Professioni selezionate
        // 'by_planet_and_profession' → intersezione Pianeta + Professione
        'audience_type',
    ];

    protected function casts(): array
    {
        return [
            'starts_at'    => 'datetime',
            'ends_at'      => 'datetime',
            'type'         => EventType::class,
            'is_published' => 'boolean',
        ];
    }

    // ── Relazioni base ───────────────────────────────────────────────────────

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_registrations')
            ->withPivot(['status', 'registered_at'])
            ->withTimestamps();
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function attendingRegistrations(): HasMany
    {
        return $this->registrations()->whereIn('status', EventAttendanceStatus::attendingValues());
    }

    public function remainingSpots(): ?int
    {
        if ($this->capacity === null) {
            return null;
        }

        return max($this->capacity - $this->attendingRegistrations()->count(), 0);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(EventInvitation::class);
    }

    // ── Audience (multi-pianeta) ─────────────────────────────────────────────

    /**
     * Pianeti a cui l'evento è riservato (usato quando audience_type = 'by_planet'
     * o 'by_planet_and_profession').
     */
    public function targetPlanets(): BelongsToMany
    {
        return $this->belongsToMany(Chapter::class, 'event_planet_targets')
            ->withTimestamps();
    }

    /**
     * Professioni a cui l'evento è riservato (usato quando audience_type = 'by_profession'
     * o 'by_planet_and_profession').
     */
    public function targetProfessions(): BelongsToMany
    {
        return $this->belongsToMany(Profession::class, 'event_profession_targets')
            ->withTimestamps();
    }

    public function targetRoles(): BelongsToMany
    {
        return $this->belongsToMany(\Spatie\Permission\Models\Role::class, 'event_role_targets', 'event_id', 'role_id')
            ->withTimestamps();
    }

    /**
     * Verifica se un utente può vedere e partecipare a questo evento.
     * Gli admin vedono sempre tutto.
     *
     * @param  User  $user
     * @param  int|null  $activePlanetId  ID del Pianeta attivo dell'utente
     * @param  array<int>  $userProfessionIds  ID delle professioni dell'utente
     * @param  array<int>  $userRoleIds  ID dei ruoli dell'utente
     */
    public function isVisibleTo(User $user, ?int $activePlanetId, array $userProfessionIds, array $userRoleIds = []): bool
    {
        // Admin e gestori-eventi vedono tutto
        if ($user->hasAnyRole(['super-admin', 'admin-community'])) {
            return true;
        }

        $type = $this->audience_type ?? 'all';

        return match ($type) {
            'all' => true,

            'by_planet' => $activePlanetId !== null
                && $this->targetPlanets->contains('id', $activePlanetId),

            'by_profession' => ! empty($userProfessionIds)
                && $this->targetProfessions->pluck('id')->intersect($userProfessionIds)->isNotEmpty(),

            'by_planet_and_profession' => $activePlanetId !== null
                && $this->targetPlanets->contains('id', $activePlanetId)
                && ! empty($userProfessionIds)
                && $this->targetProfessions->pluck('id')->intersect($userProfessionIds)->isNotEmpty(),

            'by_role' => ! empty($userRoleIds)
                && $this->targetRoles->pluck('id')->intersect($userRoleIds)->isNotEmpty(),

            'by_planet_and_role' => $activePlanetId !== null
                && $this->targetPlanets->contains('id', $activePlanetId)
                && ! empty($userRoleIds)
                && $this->targetRoles->pluck('id')->intersect($userRoleIds)->isNotEmpty(),

            'by_profession_and_role' => ! empty($userProfessionIds)
                && $this->targetProfessions->pluck('id')->intersect($userProfessionIds)->isNotEmpty()
                && ! empty($userRoleIds)
                && $this->targetRoles->pluck('id')->intersect($userRoleIds)->isNotEmpty(),

            default => true,
        };
    }

    /**
     * Label human-readable del tipo di audience, usata nell'UI.
     */
    public function audienceLabel(): string
    {
        return match ($this->audience_type ?? 'all') {
            'all'                      => 'Tutta la community',
            'by_planet'                => 'Pianeti selezionati',
            'by_profession'            => 'Professioni selezionate',
            'by_planet_and_profession' => 'Pianeti + Professioni',
            'by_role'                  => 'Ruoli selezionati',
            'by_planet_and_role'       => 'Pianeti + Ruoli',
            'by_profession_and_role'   => 'Professioni + Ruoli',
            default                    => 'Tutta la community',
        };
    }

    // ── Media ────────────────────────────────────────────────────────────────

    public function coverImageUrl(): ?string
    {
        if (! $this->cover_image) {
            return null;
        }

        if (
            str_starts_with($this->cover_image, 'http://')
            || str_starts_with($this->cover_image, 'https://')
        ) {
            return $this->cover_image;
        }

        return '/media/' . ltrim($this->cover_image, '/');
    }
}
