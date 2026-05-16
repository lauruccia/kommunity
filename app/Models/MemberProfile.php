<?php

namespace App\Models;

use App\Enums\ContactMethod;
use App\Enums\MemberProfileStatus;
use App\Support\ResolvesPublicMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class MemberProfile extends Model
{
    use HasFactory;
    use ResolvesPublicMedia;

    /**
     * Flag statico per bypassare il controllo limite per professione.
     * Impostare a true SOLO nelle azioni admin/leader, reimpostare a false subito dopo.
     *
     * Esempio d'uso:
     *   MemberProfile::$adminOverrideLimit = true;
     *   $profile->update(['active_chapter_id' => $chapterId]);
     *   MemberProfile::$adminOverrideLimit = false;
     */
    public static bool $adminOverrideLimit = false;

    protected $fillable = [
        'user_id',
        'company_name',
        'sector_id',           // Settore lavorativo scelto dall'utente
        'profession_id',       // FK primaria (usata per controllo Pianeta)
        'profession_other',
        'category_id',         // FK legacy (mantenuta per backward compat)
        'city_id',
        'region_id',
        'bio',
        'short_bio',
        'services',
        'skills',
        'networking_goals',
        'use_ai_profile_rewrite',
        'website',
        'linkedin_url',
        'facebook_url',
        'instagram_url',
        'phone',
        'whatsapp_number',
        'show_email',
        'show_phone',
        'show_whatsapp',
        'allow_whatsapp_contact',
        'preferred_contact_method',
        'avatar',
        'logo',
        'intro_video',
        'intro_video_url',
        'intro_video_duration_minutes',
        'active_chapter_id',
        'is_visible_in_directory',
        'is_active',
        'onboarding_completed',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'show_email' => 'boolean',
            'show_phone' => 'boolean',
            'show_whatsapp' => 'boolean',
            'allow_whatsapp_contact' => 'boolean',
            'use_ai_profile_rewrite' => 'boolean',
            'is_visible_in_directory' => 'boolean',
            'is_active' => 'boolean',
            'onboarding_completed' => 'boolean',
            'intro_video_duration_minutes' => 'integer',
            'preferred_contact_method' => ContactMethod::class,
            'status' => MemberProfileStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (MemberProfile $profile): void {
            // Bypass completo se l'admin ha attivato l'override
            if (static::$adminOverrideLimit) {
                return;
            }

            // Controlla il limite per professione solo se viene assegnato un Pianeta primario
            if (! $profile->active_chapter_id || ! $profile->profession_id) {
                return;
            }

            // Se active_chapter_id non è cambiato non rieseguire il controllo
            if ($profile->exists && ! $profile->isDirty('active_chapter_id') && ! $profile->isDirty('profession_id')) {
                return;
            }

            $chapter = Chapter::query()->find($profile->active_chapter_id);

            if (! $chapter) {
                return;
            }

            // Se il pianeta non ha le limitazioni attive, salta il controllo
            if (! $chapter->enforce_profession_limit) {
                return;
            }

            $assignedProfessionals = static::query()
                ->where('active_chapter_id', $profile->active_chapter_id)
                ->where('profession_id', $profile->profession_id)
                ->when($profile->exists, fn ($query) => $query->whereKeyNot($profile->getKey()))
                ->count();

            if ($assignedProfessionals >= $chapter->max_members_per_profession) {
                if ($profile->user_id) {
                    $position = $chapter->nextWaitlistPosition($profile->profession_id);

                    \DB::table('chapter_join_requests')->updateOrInsert(
                        ['chapter_id' => $profile->active_chapter_id, 'user_id' => $profile->user_id],
                        [
                            'status' => 'waitlist',
                            'waitlist_position' => $position,
                            'message' => 'Iscrizione automatica in lista d\'attesa: Pianeta al completo per questa professione.',
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }

                throw ValidationException::withMessages([
                    'active_chapter_id' => 'Il Pianeta "' . $chapter->name . '" ha raggiunto il limite di ' . $chapter->max_members_per_profession . ' professionisti per questa categoria. Il membro è stato messo in lista d\'attesa.',
                ]);
            }
        });
    }

    // ── Relazioni ────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Settore lavorativo scelto dall'utente */
    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    /** Professione primaria (usata per controllo Pianeta) */
    public function profession(): BelongsTo
    {
        return $this->belongsTo(Profession::class);
    }

    /** Tutte le professioni selezionate (M2M) */
    public function professions(): BelongsToMany
    {
        return $this->belongsToMany(Profession::class, 'member_profile_profession');
    }

    /** Categoria primaria (legacy FK) */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** Tutte le categorie selezionate (M2M — sostituisce settori) */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'member_profile_category');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Pianeta attivo (contesto corrente) — FK rinominata da chapter_id.
     * Uso esplicito della foreign key per non dipendere dalla convenzione.
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class, 'active_chapter_id');
    }

    public function companyInterestTypes(): BelongsToMany
    {
        return $this->belongsToMany(CompanyInterestType::class);
    }

    /**
     * Slot di disponibilità per One-to-One del membro.
     * Usa user_id come FK su entrambi i lati (member_profiles.user_id = availability_slots.user_id).
     */
    public function availabilitySlots(): HasMany
    {
        return $this->hasMany(AvailabilitySlot::class, 'user_id', 'user_id');
    }

    // ── Media ────────────────────────────────────────────────────────────────

    public function avatarUrl(): ?string
    {
        return $this->resolvePublicMediaUrl($this->avatar);
    }

    public function logoUrl(): ?string
    {
        return $this->resolvePublicMediaUrl($this->logo);
    }

    public function introVideoUrl(): ?string
    {
        return $this->resolvePublicMediaUrl($this->intro_video);
    }

    /** Converte YouTube/Vimeo URL in URL embed per iframe. */
    public function videoEmbedUrl(): ?string
    {
        $url = trim((string) $this->intro_video_url);
        if ($url === '') {
            return null;
        }

        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|shorts\/|live\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1] . '?rel=0&modestbranding=1&playsinline=1';
        }

        if (preg_match('/vimeo\.com\/(?:video\/|channels\/[^\/]+\/|manage\/videos\/)?(\d+)(?:\/([a-f0-9]+))?/', $url, $m)) {
            $embedUrl = 'https://player.vimeo.com/video/' . $m[1];
            $params = ['dnt=1', 'transparent=0'];
            if (! empty($m[2])) {
                $params[] = 'h=' . $m[2];
            }

            return $embedUrl . '?' . implode('&', $params);
        }

        return null;
    }

    public function prefersPortraitVideo(): bool
    {
        $url = trim((string) $this->intro_video_url);

        if ($url !== '' && str_contains($url, 'shorts/')) {
            return true;
        }

        return false;
    }

    public function hasVideo(): bool
    {
        return $this->videoEmbedUrl() !== null || $this->introVideoUrl() !== null;
    }
}
