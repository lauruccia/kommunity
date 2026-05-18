<?php

namespace App\Services;

use App\Models\BannerCampaign;
use App\Models\BannerClick;
use App\Models\BannerImpression;
use App\Models\BannerPlacement;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class BannerService
{
    public function forPlacement(string $placementKey, ?User $user = null): ?array
    {
        if (! $this->tablesReady()) {
            return null;
        }

        $placement = BannerPlacement::query()
            ->where('key', $placementKey)
            ->where('is_active', true)
            ->first();

        if (! $placement) {
            return null;
        }

        $campaigns = BannerCampaign::query()
            ->active()
            ->with([
                'creatives' => fn ($q) => $q->where('is_active', true),
                'chapters',
                'regions',
                'cities',
                'professions',
                'categories',
            ])
            ->withCount(['impressions', 'clicks'])
            ->whereHas('placements', fn ($q) => $q->where('banner_placements.id', $placement->id))
            ->get()
            ->filter(fn (BannerCampaign $campaign): bool => $this->withinCaps($campaign))
            ->filter(fn (BannerCampaign $campaign): bool => $this->matchesUser($campaign, $user))
            ->sortByDesc('priority')
            ->values();

        if ($campaigns->isEmpty()) {
            return null;
        }

        $topPriority = $campaigns->first()->priority;
        $campaign = $campaigns->where('priority', $topPriority)->random();
        $creative = $campaign->creatives->first();

        if (! $creative) {
            return null;
        }

        $this->recordImpression($campaign, $creative->id, $placementKey, $user);

        return [
            'campaign' => $campaign,
            'creative' => $creative,
            'placement_key' => $placementKey,
            'click_url' => route('banners.click', [
                'bannerCampaign' => $campaign,
                'creative' => $creative->id,
                'placement' => $placementKey,
            ]),
        ];
    }

    public function recordClick(BannerCampaign $campaign, ?int $creativeId, string $placementKey, ?User $user = null): void
    {
        if (! Schema::hasTable('banner_clicks')) {
            return;
        }

        BannerClick::query()->create([
            'banner_campaign_id' => $campaign->id,
            'banner_creative_id' => $creativeId,
            'placement_key' => $placementKey,
            'user_id' => $user?->id,
            'chapter_id' => $user?->memberProfile?->active_chapter_id,
            'clicked_at' => now(),
        ]);
    }

    private function recordImpression(BannerCampaign $campaign, ?int $creativeId, string $placementKey, ?User $user): void
    {
        if (! Schema::hasTable('banner_impressions')) {
            return;
        }

        BannerImpression::query()->create([
            'banner_campaign_id' => $campaign->id,
            'banner_creative_id' => $creativeId,
            'placement_key' => $placementKey,
            'user_id' => $user?->id,
            'chapter_id' => $user?->memberProfile?->active_chapter_id,
            'shown_at' => now(),
        ]);
    }

    private function withinCaps(BannerCampaign $campaign): bool
    {
        if ($campaign->max_impressions !== null && $campaign->impressions_count >= $campaign->max_impressions) {
            return false;
        }

        if ($campaign->max_clicks !== null && $campaign->clicks_count >= $campaign->max_clicks) {
            return false;
        }

        return true;
    }

    private function matchesUser(BannerCampaign $campaign, ?User $user): bool
    {
        if (! $user) {
            return $this->hasNoTargets($campaign);
        }

        if ($this->hasNoTargets($campaign)) {
            return true;
        }

        $profile = $user->memberProfile;
        $checks = [
            $profile?->active_chapter_id && $campaign->chapters->contains('id', $profile->active_chapter_id),
            $profile?->region_id && $campaign->regions->contains('id', $profile->region_id),
            $profile?->city_id && $campaign->cities->contains('id', $profile->city_id),
            $profile?->profession_id && $campaign->professions->contains('id', $profile->profession_id),
            $profile && $profile->categories->isNotEmpty() && $campaign->categories->pluck('id')->intersect($profile->categories->pluck('id'))->isNotEmpty(),
            $profile && $profile->professions->isNotEmpty() && $campaign->professions->pluck('id')->intersect($profile->professions->pluck('id'))->isNotEmpty(),
        ];

        return collect($checks)->contains(true);
    }

    private function hasNoTargets(BannerCampaign $campaign): bool
    {
        return $campaign->chapters->isEmpty()
            && $campaign->regions->isEmpty()
            && $campaign->cities->isEmpty()
            && $campaign->professions->isEmpty()
            && $campaign->categories->isEmpty();
    }

    private function tablesReady(): bool
    {
        return Schema::hasTable('banner_campaigns')
            && Schema::hasTable('banner_placements')
            && Schema::hasTable('banner_creatives')
            && Schema::hasTable('banner_campaign_placement')
            && Schema::hasTable('banner_impressions')
            && Schema::hasTable('banner_clicks');
    }
}
