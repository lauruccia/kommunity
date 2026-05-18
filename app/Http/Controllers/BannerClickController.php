<?php

namespace App\Http\Controllers;

use App\Models\BannerCampaign;
use App\Services\BannerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BannerClickController extends Controller
{
    public function __invoke(Request $request, BannerCampaign $bannerCampaign, BannerService $bannerService): RedirectResponse
    {
        $bannerService->recordClick(
            $bannerCampaign,
            $request->integer('creative') ?: null,
            $request->string('placement')->toString() ?: 'unknown',
            $request->user()
        );

        return redirect()->away($bannerCampaign->target_url);
    }
}
