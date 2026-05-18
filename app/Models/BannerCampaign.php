<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BannerCampaign extends Model
{
    protected $fillable = [
        'advertiser_id',
        'name',
        'status',
        'sales_package',
        'starts_at',
        'ends_at',
        'priority',
        'price',
        'max_impressions',
        'max_clicks',
        'target_url',
        'open_in_new_tab',
        'target_mode',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'open_in_new_tab' => 'boolean',
            'priority' => 'integer',
            'price' => 'decimal:2',
            'max_impressions' => 'integer',
            'max_clicks' => 'integer',
        ];
    }

    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(Advertiser::class);
    }

    public function creatives(): HasMany
    {
        return $this->hasMany(BannerCreative::class);
    }

    public function placements(): BelongsToMany
    {
        return $this->belongsToMany(BannerPlacement::class, 'banner_campaign_placement');
    }

    public function chapters(): BelongsToMany
    {
        return $this->belongsToMany(Chapter::class, 'banner_campaign_chapter');
    }

    public function regions(): BelongsToMany
    {
        return $this->belongsToMany(Region::class, 'banner_campaign_region');
    }

    public function cities(): BelongsToMany
    {
        return $this->belongsToMany(City::class, 'banner_campaign_city');
    }

    public function professions(): BelongsToMany
    {
        return $this->belongsToMany(Profession::class, 'banner_campaign_profession');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'banner_campaign_category');
    }

    public function impressions(): HasMany
    {
        return $this->hasMany(BannerImpression::class);
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(BannerClick::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', 'active')
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    public function targetSummary(): string
    {
        $parts = [];

        foreach ([
            'Pianeti' => $this->chapters,
            'Regioni' => $this->regions,
            'Citta' => $this->cities,
            'Professioni' => $this->professions,
            'Categorie' => $this->categories,
        ] as $label => $items) {
            if ($items->isNotEmpty()) {
                $parts[] = $label . ': ' . $items->pluck('name')->join(', ');
            }
        }

        return $parts === [] ? 'Globale' : implode(' | ', $parts);
    }
}
