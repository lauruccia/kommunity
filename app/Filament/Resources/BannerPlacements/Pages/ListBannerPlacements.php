<?php

namespace App\Filament\Resources\BannerPlacements\Pages;

use App\Filament\Resources\BannerPlacements\BannerPlacementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBannerPlacements extends ListRecords
{
    protected static string $resource = BannerPlacementResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
