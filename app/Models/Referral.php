<?php

namespace App\Models;

use App\Enums\ReferralStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'recipient_id',
        'client_user_id',
        'title',
        'description',
        'company_name',
        'contact_name',
        'estimated_value',
        'declared_value',
        'declared_at',
        'client_confirmed_at',
        'approved_value',
        'approved_at',
        'approved_by',
        'priority',
        'status',
        'notes',
        'outcome',
        'is_public',
        'acknowledged_at',
    ];

    protected function casts(): array
    {
        return [
            'estimated_value' => 'decimal:2',
            'declared_value'  => 'decimal:2',
            'approved_value'  => 'decimal:2',
            'declared_at'         => 'datetime',
            'client_confirmed_at' => 'datetime',
            'approved_at'         => 'datetime',
            'status'          => ReferralStatus::class,
            'is_public'       => 'boolean',
            'acknowledged_at' => 'datetime',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Il cliente segnalato (il membro che ha bisogno del servizio, es. Fabbro).
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Valore di riferimento: approvato se validato, altrimenti dichiarato, altrimenti stimato.
     */
    public function effectiveValue(): ?float
    {
        $value = $this->approved_value ?? $this->declared_value ?? $this->estimated_value;

        return $value !== null ? (float) $value : null;
    }

    /**
     * Valore che concorre alla classifica/premi (solo se confermato dall'admin).
     */
    public function scorableValue(): float
    {
        return $this->status->countsForScore()
            ? (float) ($this->approved_value ?? $this->declared_value ?? 0)
            : 0.0;
    }

    /**
     * Scope: referenze confermate (valore validato) in un eventuale intervallo.
     */
    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->whereIn('status', [
            ReferralStatus::Confirmed->value,
            ReferralStatus::Won->value,
        ]);
    }
}
