<?php

namespace App\Filament\Resources\CompanyInterestTypes\Pages;

use App\Filament\Resources\CompanyInterestTypes\CompanyInterestTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCompanyInterestTypes extends ListRecords
{
    protected static string $resource = CompanyInterestTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
