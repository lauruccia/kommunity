<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BannerPlacement extends Model
{
    protected $fillable = ['key', 'label', 'section', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(BannerCampaign::class, 'banner_campaign_placement');
    }
}
