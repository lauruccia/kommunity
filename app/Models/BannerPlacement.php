<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BannerPlacement extends Model
{
    protected $fillable = [
        'key',
        'label',
        'section',
        'desktop_width',
        'desktop_height',
        'mobile_width',
        'mobile_height',
        'max_file_size_kb',
        'allowed_formats',
        'mobile_required',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'allowed_formats' => 'array',
            'mobile_required' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(BannerCampaign::class, 'banner_campaign_placement');
    }

    public function desktopRatio(): ?float
    {
        if (! $this->desktop_width || ! $this->desktop_height) {
            return null;
        }

        return $this->desktop_width / $this->desktop_height;
    }

    public function mobileRatio(): ?float
    {
        if (! $this->mobile_width || ! $this->mobile_height) {
            return null;
        }

        return $this->mobile_width / $this->mobile_height;
    }
}
