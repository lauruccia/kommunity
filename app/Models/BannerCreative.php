<?php

namespace App\Models;

use App\Support\ResolvesPublicMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BannerCreative extends Model
{
    use ResolvesPublicMedia;

    protected $fillable = [
        'banner_campaign_id',
        'image_desktop',
        'image_mobile',
        'alt_text',
        'headline',
        'placement_size',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(BannerCampaign::class, 'banner_campaign_id');
    }

    public function desktopImageUrl(): ?string
    {
        return $this->resolvePublicMediaUrl($this->image_desktop);
    }

    public function mobileImageUrl(): ?string
    {
        return $this->resolvePublicMediaUrl($this->image_mobile);
    }
}
