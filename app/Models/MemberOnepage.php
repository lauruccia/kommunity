<?php

namespace App\Models;

use App\Enums\OnepageVisibility;
use App\Support\ResolvesPublicMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    /**
     * Profilo membro collegato (stesso user_id).
     */
    public function profile(): HasOne
    {
        return $this->hasOne(MemberProfile::class, 'user_id', 'user_id');
    }

    public function coverImageUrl(): ?string
    {
        return $this->resolvePublicMediaUrl($this->cover_image);
    }
}
