<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'endpoint',
        'endpoint_hash',
        'p256dh_key',
        'auth_key',
        'user_agent',
        'last_used_at',
        'revoked_at',
        'failure_count',
    ];

    protected function casts(): array
    {
        return [
            'last_used_at'  => 'datetime',
            'revoked_at'    => 'datetime',
            'failure_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('revoked_at');
    }

    /**
     * Calcola l'hash deterministico dell'endpoint per la UNIQUE key.
     */
    public static function hashEndpoint(string $endpoint): string
    {
        return hash('sha256', $endpoint);
    }

    /**
     * Marca la subscription come revocata (es. dopo HTTP 410 Gone).
     */
    public function revoke(?string $reason = null): void
    {
        $this->forceFill([
            'revoked_at'    => now(),
            'failure_count' => $this->failure_count + 1,
        ])->save();
    }
}
