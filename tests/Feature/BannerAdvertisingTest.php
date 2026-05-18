<?php

namespace Tests\Feature;

use App\Models\Advertiser;
use App\Models\BannerCampaign;
use App\Models\BannerCreative;
use App\Models\BannerPlacement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BannerAdvertisingTest extends TestCase
{
    use RefreshDatabase;

    public function test_directory_banner_records_impression_and_click(): void
    {
        $user = User::factory()->create();
        $this->readyMember($user);

        $placement = BannerPlacement::where('key', 'directory_top')->firstOrFail();
        $campaign = $this->campaignForPlacement($placement);
        $creative = BannerCreative::create([
            'banner_campaign_id' => $campaign->id,
            'image_desktop' => 'banners/test.jpg',
            'alt_text' => 'Banner demo',
            'placement_size' => 'leaderboard',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('directory.index'))
            ->assertOk()
            ->assertSee('Banner demo');

        $this->assertDatabaseHas('banner_impressions', [
            'banner_campaign_id' => $campaign->id,
            'banner_creative_id' => $creative->id,
            'placement_key' => 'directory_top',
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('banners.click', [
                'bannerCampaign' => $campaign,
                'creative' => $creative->id,
                'placement' => 'directory_top',
            ]))
            ->assertRedirect('https://example.com/offerta');

        $this->assertDatabaseHas('banner_clicks', [
            'banner_campaign_id' => $campaign->id,
            'banner_creative_id' => $creative->id,
            'placement_key' => 'directory_top',
            'user_id' => $user->id,
        ]);
    }

    public function test_admin_can_export_campaign_report_csv(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('super-admin');
        $admin->assignRole('super-admin');

        $placement = BannerPlacement::where('key', 'directory_sidebar')->firstOrFail();
        $campaign = $this->campaignForPlacement($placement);
        BannerCreative::create([
            'banner_campaign_id' => $campaign->id,
            'image_desktop' => 'banners/sidebar.jpg',
            'alt_text' => 'Sidebar demo',
            'placement_size' => 'sidebar',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.banner-campaigns.export', $campaign));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
    }

    private function campaignForPlacement(BannerPlacement $placement): BannerCampaign
    {
        $advertiser = Advertiser::create([
            'name' => 'Demo Advertiser',
            'email' => 'adv@example.com',
        ]);

        $campaign = BannerCampaign::create([
            'advertiser_id' => $advertiser->id,
            'name' => 'Campagna Demo',
            'status' => 'active',
            'sales_package' => 'global',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addWeek(),
            'priority' => 10,
            'target_url' => 'https://example.com/offerta',
            'open_in_new_tab' => true,
        ]);

        $campaign->placements()->sync([$placement->id]);

        return $campaign;
    }

    private function readyMember(User $user): void
    {
        $user->memberProfile()->update([
            'onboarding_completed' => true,
            'is_active' => true,
            'status' => 'active',
        ]);
    }
}
