<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileVideoAccessRequest extends Model
{
    protected $fillable = [
        'requester_id',
        'recipient_id',
        'status',
        'requested_at',
        'responded_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'responded_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function scopeBetween($query, int $firstUserId, int $secondUserId)
    {
        return $query->where(function ($inner) use ($firstUserId, $secondUserId): void {
            $inner
                ->where('requester_id', $firstUserId)
                ->where('recipient_id', $secondUserId);
        })->orWhere(function ($inner) use ($firstUserId, $secondUserId): void {
            $inner
                ->where('requester_id', $secondUserId)
                ->where('recipient_id', $firstUserId);
        });
    }

    public static function grantsAccessBetween(int $firstUserId, int $secondUserId): bool
    {
        return static::query()
            ->between($firstUserId, $secondUserId)
            ->where('status', 'accepted')
            ->exists();
    }
}
