<?php

namespace App\Filament\Resources\BannerCreatives\Pages;

use App\Filament\Resources\BannerCreatives\BannerCreativeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBannerCreatives extends ListRecords
{
    protected static string $resource = BannerCreativeResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
