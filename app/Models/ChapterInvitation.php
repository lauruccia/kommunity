<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Invito diretto al Pianeta emesso da un leader o admin.
 *
 * Flusso completo:
 *   1. Leader crea l'invito → token generato automaticamente.
 *   2. Email inviata all'indirizzo con il link /invita/{token}.
 *   3. Utente si registra tramite il link.
 *   4. Dopo registrazione: status = 'accepted', utente assegnato al Pianeta.
 *
 * Status:
 *   pending  → invito inviato, non ancora usato
 *   accepted → utente registrato e assegnato al Pianeta
 *   expired  → link scaduto (expires_at superata)
 *   revoked  → annullato manualmente dall'admin
 */
class ChapterInvitation extends Model
{
    protected $fillable = [
        'chapter_id',
        'invited_by_user_id',
        'email',
        'token',
        'status',
        'message',
        'expires_at',
        'accepted_at',
        'accepted_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'expires_at'  => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    // ── Boot ─────────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (ChapterInvitation $invitation): void {
            if (empty($invitation->token)) {
                $invitation->token = Str::random(40);
            }
        });
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeValid($query)
    {
        return $query->where('status', 'pending')
            ->where(fn ($q) => $q
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>', now())
            );
    }

    // ── Relazioni ────────────────────────────────────────────────────────────

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by_user_id');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** URL completo del link di invito. */
    public function inviteUrl(): string
    {
        return route('chapter.invite', ['token' => $this->token]);
    }

    /** Verifica se l'invito è ancora utilizzabile. */
    public function isValid(): bool
    {
        return $this->status === 'pending'
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    /** Accetta l'invito per un utente appena registrato. */
    public function accept(User $user): void
    {
        $this->update([
            'status'              => 'accepted',
            'accepted_at'         => now(),
            'accepted_by_user_id' => $user->id,
        ]);

        // Assegna il membro al Pianeta come pianeta attivo senza controllo limite (invito diretto)
        $user->memberProfile?->updateWithAdminOverride(['active_chapter_id' => $this->chapter_id]);

        // Registra anche in chapter_members
        \DB::table('chapter_members')->updateOrInsert(
            ['chapter_id' => $this->chapter_id, 'user_id' => $user->id],
            ['status' => 'active', 'joined_at' => now(), 'updated_at' => now(), 'created_at' => now()]
        );

        // Crea un join request marcato come accepted (tracciabilità)
        ChapterJoinRequest::updateOrCreate(
            ['chapter_id' => $this->chapter_id, 'user_id' => $user->id],
            [
                'status'              => 'accepted',
                'invited_by_user_id'  => $this->invited_by_user_id,
                'reviewed_at'         => now(),
                'message'             => 'Iscrizione tramite invito diretto.',
            ]
        );
    }

    /** Etichetta italiana dello status. */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending'  => 'In attesa',
            'accepted' => 'Accettato',
            'expired'  => 'Scaduto',
            'revoked'  => 'Revocato',
            default    => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'pending'  => 'warning',
            'accepted' => 'success',
            'expired'  => 'gray',
            'revoked'  => 'danger',
            default    => 'gray',
        };
    }
}
