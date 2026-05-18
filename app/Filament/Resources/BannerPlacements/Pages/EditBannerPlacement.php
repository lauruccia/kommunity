<?php

namespace App\Filament\Resources\BannerPlacements\Pages;

use App\Filament\Resources\BannerPlacements\BannerPlacementResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBannerPlacement extends EditRecord
{
    protected static string $resource = BannerPlacementResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
