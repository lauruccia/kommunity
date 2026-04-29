<?php

namespace App\Models;

use App\Enums\EventAttendanceStatus;
use App\Enums\EventType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'chapter_id',
        'organizer_id',
        'title',
        'slug',
        'description',
        'cover_image',
        'type',
        'starts_at',
        'ends_at',
        'location',
        'meeting_url',
        'capacity',
        'status',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'type' => EventType::class,
            'is_published' => 'boolean',
        ];
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_registrations')
            ->withPivot(['status', 'registered_at'])
            ->withTimestamps();
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function attendingRegistrations(): HasMany
    {
        return $this->registrations()->whereIn('status', EventAttendanceStatus::attendingValues());
    }

    public function remainingSpots(): ?int
    {
        if ($this->capacity === null) {
            return null;
        }

        return max($this->capacity - $this->attendingRegistrations()->count(), 0);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(EventInvitation::class);
    }

    public function coverImageUrl(): ?string
    {
        if (! $this->cover_image) {
            return null;
        }

        if (
            str_starts_with($this->cover_image, 'http://')
            || str_starts_with($this->cover_image, 'https://')
        ) {
            return $this->cover_image;
        }

        return '/media/' . ltrim($this->cover_image, '/');
    }
}
