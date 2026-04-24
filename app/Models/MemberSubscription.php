<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberSubscription extends Model
{
    protected $fillable = [
        'user_id', 'plan_id', 'status',
        'payment_method', 'payment_reference', 'payment_notes',
        'requested_at', 'trial_ends_at', 'starts_at', 'ends_at',
        'approved_by', 'approved_at', 'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'status'         => SubscriptionStatus::class,
            'payment_method' => PaymentMethod::class,
            'requested_at'   => 'datetime',
            'trial_ends_at'  => 'datetime',
            'starts_at'      => 'datetime',
            'ends_at'        => 'datetime',
            'approved_at'    => 'datetime',
        ];
    }

    // ── Relazioni ──────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /** L'abbonamento dà accesso (attivo o in prova valida) */
    public function isAccessible(): bool
    {
        if (! $this->status->isAccessible()) {
            return false;
        }
        // Controlla scadenza
        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }
        if ($this->status === SubscriptionStatus::Trial && $this->trial_ends_at?->isPast()) {
            return false;
        }
        return true;
    }

    public function hasDirectoryAccess(): bool
    {
        return $this->isAccessible();
    }

    public function hasPageAccess(): bool
    {
        return $this->isAccessible() && $this->plan?->includesPage();
    }

    public function isExpired(): bool
    {
        if ($this->ends_at && $this->ends_at->isPast()) return true;
        if ($this->status === SubscriptionStatus::Trial && $this->trial_ends_at?->isPast()) return true;
        return false;
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            SubscriptionStatus::Active->value,
            SubscriptionStatus::Trial->value,
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', SubscriptionStatus::Pending->value);
    }
}
