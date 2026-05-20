<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use HasRoles;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'referral_code',
        'invited_by_user_id',
        'invited_by_name',
        'locale',
        'last_seen_at',
        'last_seen_url',
        'last_seen_route',
        'show_online_status',
        'show_read_receipts',
        // ── Concierge onboarding (feature: concierge_onboarding) ────────────
        'concierge_assigned_at',
        'concierge_assigned_to',
        'concierge_completed_at',
        'concierge_notes',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'      => 'datetime',
            'password'               => 'hashed',
            'concierge_assigned_at'  => 'datetime',
            'concierge_completed_at' => 'datetime',
            'last_seen_at'           => 'datetime',
            'show_online_status'     => 'boolean',
            'show_read_receipts'     => 'boolean',
        ];
    }

    /**
     * Admin Concierge: chi sta gestendo l'onboarding di questo membro.
     */
    public function conciergeAssignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'concierge_assigned_to');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(['super-admin', 'admin-community', 'leader-capitolo'])
            || $this->hasAnyPermission([
                'gestire-eventi',
                'gestire-utenti',
                'assegnare-ruoli',
                'assegnare-permessi',
                'gestire-capitoli',
            ]);
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification());
    }

    public function memberProfile(): HasOne
    {
        return $this->hasOne(MemberProfile::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public function invitedUsers(): HasMany
    {
        return $this->hasMany(User::class, 'invited_by_user_id');
    }

    public function memberOnepage(): HasOne
    {
        return $this->hasOne(MemberOnepage::class);
    }

    public function memberGalleryImages(): HasMany
    {
        return $this->hasMany(MemberGalleryImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function availabilitySlots(): HasMany
    {
        return $this->hasMany(AvailabilitySlot::class);
    }

    public function sentOneToOneRequests(): HasMany
    {
        return $this->hasMany(OneToOneRequest::class, 'requester_id');
    }

    public function receivedOneToOneRequests(): HasMany
    {
        return $this->hasMany(OneToOneRequest::class, 'recipient_id');
    }

    public function organizedEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'organizer_id');
    }

    public function registeredEvents(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_registrations')
            ->withPivot(['status', 'registered_at'])
            ->withTimestamps();
    }

    public function eventRegistrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function sentReferrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'sender_id');
    }

    public function receivedReferrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'recipient_id');
    }

    public function forumThreads(): HasMany
    {
        return $this->hasMany(ForumThread::class);
    }

    public function forumPosts(): HasMany
    {
        return $this->hasMany(ForumPost::class);
    }

    public function forumCategoryProposals(): HasMany
    {
        return $this->hasMany(ForumCategoryProposal::class);
    }

    public function profileSuggestions(): HasMany
    {
        return $this->hasMany(ProfileSuggestion::class);
    }

    public function sentProfileVideoAccessRequests(): HasMany
    {
        return $this->hasMany(ProfileVideoAccessRequest::class, 'requester_id');
    }

    public function receivedProfileVideoAccessRequests(): HasMany
    {
        return $this->hasMany(ProfileVideoAccessRequest::class, 'recipient_id');
    }

    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot(['last_read_at'])
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    // ── Pianeti (multi-pianeta) ────────────────────────────────────────────

    /**
     * Tutti i Pianeti a cui l'utente appartiene (via chapter_members).
     * Un utente può appartenere a più Pianeti contemporaneamente.
     */
    public function planets(): BelongsToMany
    {
        return $this->belongsToMany(Chapter::class, 'chapter_members')
            ->withPivot(['status', 'joined_at'])
            ->withTimestamps()
            ->wherePivot('status', 'active');
    }

    /**
     * ID del Pianeta attivo (contesto corrente di navigazione).
     * Corrisponde a member_profiles.active_chapter_id.
     */
    public function activePlanetId(): ?int
    {
        return $this->memberProfile?->active_chapter_id;
    }

    /**
     * Pianeta attivo come model Chapter.
     */
    public function activePlanet(): ?Chapter
    {
        $id = $this->activePlanetId();
        if (! $id) {
            return null;
        }

        return $this->planets->firstWhere('id', $id)
            ?? Chapter::find($id);
    }

    /**
     * Verifica se l'utente ha un dato permesso all'interno di uno specifico Pianeta.
     *
     * Il permesso è definito sul ruolo globale (PlanetRole) assegnato all'utente
     * in quel pianeta tramite chapter_member_roles.
     *
     * Esempio:
     *   $user->hasPlanetPermission('forum.moderate', $planetId)
     *   $user->hasPlanetPermission('members.invite', auth()->user()->activePlanetId())
     *
     * Super-admin e admin-community hanno sempre tutti i permessi.
     */
    public function hasPlanetPermission(string $permission, int $planetId): bool
    {
        // Gli admin hanno sempre tutto
        if ($this->hasAnyRole(['super-admin', 'admin-community'])) {
            return true;
        }

        $roleId = \DB::table('chapter_member_roles')
            ->where('chapter_id', $planetId)
            ->where('user_id', $this->id)
            ->value('role_id');

        if (! $roleId) {
            return false;
        }

        $role = \App\Models\PlanetRole::find($roleId);

        return $role?->hasPermission($permission) ?? false;
    }

    /**
     * Restituisce il PlanetRole dell'utente in un dato Pianeta, o null.
     */
    public function planetRole(int $planetId): ?\App\Models\PlanetRole
    {
        $roleId = \DB::table('chapter_member_roles')
            ->where('chapter_id', $planetId)
            ->where('user_id', $this->id)
            ->value('role_id');

        return $roleId ? \App\Models\PlanetRole::find($roleId) : null;
    }

    public function referralRegistrationUrl(): string
    {
        $this->ensureReferralCode();

        return route('register', ['ref' => $this->referral_code], false);
    }

    public function ensureReferralCode(): string
    {
        if (filled($this->referral_code)) {
            return $this->referral_code;
        }

        $base = Str::slug($this->name, '');

        if ($base === '') {
            $base = 'membro';
        }

        $code = $base;
        $index = 2;

        while (static::query()
            ->where('referral_code', $code)
            ->when($this->exists, fn ($query) => $query->whereKeyNot($this->getKey()))
            ->exists()) {
            $code = $base.$index;
            $index++;
        }

        $this->forceFill(['referral_code' => $code])->saveQuietly();

        return $code;
    }

    // ── Abbonamenti ────────────────────────────────────────────────────────

    public function subscriptions(): HasMany
    {
        return $this->hasMany(MemberSubscription::class);
    }

    /** Abbonamento attivo (o in prova) più recente */
    public function activeSubscription(): ?MemberSubscription
    {
        return $this->subscriptions()
            ->whereIn('status', [SubscriptionStatus::Active->value, SubscriptionStatus::Trial->value])
            ->latest()
            ->first();
    }

    /** Abbonamento in attesa di approvazione */
    public function pendingSubscription(): ?MemberSubscription
    {
        return $this->subscriptions()
            ->where('status', SubscriptionStatus::Pending->value)
            ->latest()
            ->first();
    }

    public function hasActiveSubscription(): bool
    {
        $sub = $this->activeSubscription();
        return $sub !== null && $sub->isAccessible();
    }

    public function hasDirectoryAccess(): bool
    {
        return $this->hasActiveSubscription();
    }

    public function hasPageAccess(): bool
    {
        $sub = $this->activeSubscription();
        return $sub !== null && $sub->hasPageAccess();
    }
}
