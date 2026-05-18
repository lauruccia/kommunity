<?php

namespace App\Filament\Resources\BannerCreatives\Pages;

use App\Filament\Resources\BannerCreatives\BannerCreativeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBannerCreative extends EditRecord
{
    protected static string $resource = BannerCreativeResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
