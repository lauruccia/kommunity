<?php

namespace App\Models;

use App\Enums\OneToOneStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OneToOneRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'requester_id',
        'recipient_id',
        'availability_slot_id',
        'requested_at',
        'meeting_mode',
        'meeting_link',
        'meeting_location',
        'goal',
        'pre_notes',
        'post_notes',
        'requester_completed_at',
        'recipient_completed_at',
        'completed_at',
        'follow_up_notes',
        'status',
        'rescheduled_by',
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'requester_completed_at' => 'datetime',
            'recipient_completed_at' => 'datetime',
            'completed_at' => 'datetime',
            'status' => OneToOneStatus::class,
        ];
    }

    public function completionConfirmedBy(int $userId): bool
    {
        if ($this->requester_id === $userId) {
            return $this->requester_completed_at !== null;
        }

        if ($this->recipient_id === $userId) {
            return $this->recipient_completed_at !== null;
        }

        return false;
    }

    public function canBeConfirmedBy(int $userId): bool
    {
        return in_array($userId, [$this->requester_id, $this->recipient_id], true)
            && $this->status === OneToOneStatus::Accepted
            && ! $this->completionConfirmedBy($userId);
    }

    public function isFullyConfirmed(): bool
    {
        return $this->requester_completed_at !== null
            && $this->recipient_completed_at !== null;
    }

    /**
     * Almeno un partecipante ha confermato il completamento.
     * Quando è true non è più possibile riprogrammare/annullare l'incontro.
     */
    public function completionStarted(): bool
    {
        return $this->requester_completed_at !== null
            || $this->recipient_completed_at !== null;
    }

    /**
     * L'utente può accettare/rifiutare la richiesta?
     * - status Pending      → solo il destinatario
     * - status Rescheduled  → la parte che NON ha proposto la riprogrammazione
     *                         (fallback legacy: il destinatario se rescheduled_by è NULL)
     */
    public function canRespondTo(int $userId): bool
    {
        if ($this->status === OneToOneStatus::Pending) {
            return $this->recipient_id === $userId;
        }

        if ($this->status === OneToOneStatus::Rescheduled) {
            return $this->rescheduled_by
                ? (int) $this->rescheduled_by !== $userId
                : $this->recipient_id === $userId;
        }

        return false;
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function availabilitySlot(): BelongsTo
    {
        return $this->belongsTo(AvailabilitySlot::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(OneToOneNote::class);
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(OneToOneFollowup::class);
    }

    public function references(): HasMany
    {
        return $this->hasMany(OneToOneReference::class);
    }
}
