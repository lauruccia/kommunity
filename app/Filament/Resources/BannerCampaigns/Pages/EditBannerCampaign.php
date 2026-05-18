<?php

namespace App\Filament\Resources\BannerCampaigns\Pages;

use App\Filament\Resources\BannerCampaigns\BannerCampaignResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBannerCampaign extends EditRecord
{
    protected static string $resource = BannerCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
