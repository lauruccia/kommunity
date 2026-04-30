<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
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

    public function referralRegistrationUrl(): string
    {
        return route('register', ['ref' => $this->referral_code], false);
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
