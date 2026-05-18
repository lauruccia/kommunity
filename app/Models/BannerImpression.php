<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BannerImpression extends Model
{
    protected $fillable = [
        'banner_campaign_id',
        'banner_creative_id',
        'placement_key',
        'user_id',
        'chapter_id',
        'shown_at',
    ];

    protected function casts(): array
    {
        return ['shown_at' => 'datetime'];
    }
}
