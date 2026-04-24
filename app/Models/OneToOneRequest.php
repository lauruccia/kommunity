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
        'follow_up_notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'status' => OneToOneStatus::class,
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
}
