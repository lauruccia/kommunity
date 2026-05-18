<?php

namespace App\Filament\Resources\BannerCampaigns\Pages;

use App\Filament\Resources\BannerCampaigns\BannerCampaignResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBannerCampaigns extends ListRecords
{
    protected static string $resource = BannerCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
