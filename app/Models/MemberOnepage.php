<?php

namespace App\Models;

use App\Enums\OnepageVisibility;
use App\Support\ResolvesPublicMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberOnepage extends Model
{
    use HasFactory;
    use ResolvesPublicMedia;

    protected $fillable = [
        'user_id',
        'slug',
        'title',
        'hero_title',
        'hero_subtitle',
        'intro_text',
        'about_text',
        'services_text',
        'cta_text',
        'cover_image',
        'template',
        'is_active',
        'visibility',
        'seo_title',
        'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'visibility' => OnepageVisibility::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coverImageUrl(): ?string
    {
        return $this->resolvePublicMediaUrl($this->cover_image);
    }
}
