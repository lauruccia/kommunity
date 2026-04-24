<?php

namespace App\Filament\Resources\CompanyInterestTypes\Pages;

use App\Filament\Resources\CompanyInterestTypes\CompanyInterestTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCompanyInterestType extends EditRecord
{
    protected static string $resource = CompanyInterestTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
