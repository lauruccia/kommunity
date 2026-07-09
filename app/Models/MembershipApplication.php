<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Candidatura di ammissione a Kommunity inviata da un visitatore
 * NON ancora registrato (dalla card di un membro o dalla homepage).
 *
 * Diversa da ChapterJoinRequest, che riguarda utenti GIÀ registrati
 * che chiedono di entrare in un Pianeta.
 *
 * Status possibili:
 *   pending   → in attesa di revisione admin
 *   approved  → approvata: creato lo User e iscritto al Pianeta
 *   rejected  → rifiutata dall'admin
 */
class MembershipApplication extends Model
{
    public const SOURCE_CARD = 'card';
    public const SOURCE_HOME = 'home';

    public const TYPE_PRIVATE = 'privato';
    public const TYPE_COMPANY = 'azienda';

    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'source',
        'presenter_user_id',
        'chapter_id',
        'name',
        'email',
        'phone',
        'applicant_type',
        'vat_number',
        'profession',
        'referrer_name',
        'locale',
        'status',
        'rejection_reason',
        'reviewed_by_user_id',
        'reviewed_at',
        'created_user_id',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    // ── Relazioni ────────────────────────────────────────────────────────────

    /** Membro che presenta il candidato (proprietario della card). */
    public function presenter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'presenter_user_id');
    }

    /** Pianeta proposto per l'ammissione. */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    /** Admin che ha revisionato la candidatura. */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    /** Utente creato all'approvazione. */
    public function createdUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_user_id');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function isCompany(): bool
    {
        return $this->applicant_type === self::TYPE_COMPANY;
    }

    /** Locale sicuro per le email al candidato (solo lingue tradotte). */
    public function mailLocale(): string
    {
        return $this->locale === 'it' ? 'it' : 'en';
    }

    /** Etichetta italiana dello status per la UI admin. */
    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING  => 'In attesa',
            self::STATUS_APPROVED => 'Approvata',
            self::STATUS_REJECTED => 'Rifiutata',
            default               => ucfirst($this->status),
        };
    }

    /** Colore badge Filament per lo status. */
    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING  => 'warning',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            default               => 'gray',
        };
    }

    /** Etichetta italiana della provenienza per la UI admin. */
    public function sourceLabel(): string
    {
        return match ($this->source) {
            self::SOURCE_CARD => 'Card membro',
            self::SOURCE_HOME => 'Homepage',
            default           => ucfirst($this->source),
        };
    }
}
