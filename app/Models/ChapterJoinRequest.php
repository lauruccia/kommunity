<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Richiesta di iscrizione a un Pianeta.
 *
 * Status possibili:
 *   pending   → in attesa di revisione da admin/leader
 *   accepted  → approvata, membro assegnato al Pianeta
 *   rejected  → rifiutata dall'admin
 *   waitlist  → limite per professione raggiunto, in lista d'attesa
 *   moved     → spostato su un altro Pianeta dall'admin
 */
class ChapterJoinRequest extends Model
{
    protected $fillable = [
        'chapter_id',
        'user_id',
        'message',
        'status',
        'waitlist_position',
        'waitlist_notified_at',
        'admin_override',
        'invited_by_user_id',
        'reviewed_by_user_id',
        'reviewed_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'admin_override'       => 'boolean',
            'waitlist_position'    => 'integer',
            'waitlist_notified_at' => 'datetime',
            'reviewed_at'          => 'datetime',
        ];
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeWaitlist($query)
    {
        return $query->where('status', 'waitlist');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    // ── Relazioni ────────────────────────────────────────────────────────────

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Chi ha inviato l'invito (leader/admin). Null se richiesta spontanea. */
    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    /** Admin/leader che ha revisionato la richiesta. */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** Etichetta italiana dello status per la UI. */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending'  => 'In attesa',
            'accepted' => 'Accettata',
            'rejected' => 'Rifiutata',
            'waitlist' => 'Lista d\'attesa',
            'moved'    => 'Spostato',
            default    => ucfirst($this->status),
        };
    }

    /** Colore badge Filament per lo status. */
    public function statusColor(): string
    {
        return match ($this->status) {
            'pending'  => 'warning',
            'accepted' => 'success',
            'rejected' => 'danger',
            'waitlist' => 'info',
            'moved'    => 'gray',
            default    => 'gray',
        };
    }
}
