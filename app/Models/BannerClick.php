<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BannerClick extends Model
{
    protected $fillable = [
        'banner_campaign_id',
        'banner_creative_id',
        'placement_key',
        'user_id',
        'chapter_id',
        'clicked_at',
    ];

    protected function casts(): array
    {
        return ['clicked_at' => 'datetime'];
    }
}
