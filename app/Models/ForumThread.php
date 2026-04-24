<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ForumThread extends Model
{
    use HasFactory;

    protected $fillable = [
        'forum_category_id',
        'user_id',
        'title',
        'slug',
        'excerpt',
        'is_pinned',
        'is_locked',
    ];

    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
            'is_locked' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ForumCategory::class, 'forum_category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(ForumPost::class);
    }

    public function firstPost(): HasOne
    {
        return $this->hasOne(ForumPost::class)->oldestOfMany();
    }

    public function latestPost(): HasOne
    {
        return $this->hasOne(ForumPost::class)->latestOfMany();
    }
}
