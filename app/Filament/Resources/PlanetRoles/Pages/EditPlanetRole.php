<?php

namespace App\Filament\Resources\PlanetRoles\Pages;

use App\Filament\Resources\PlanetRoles\PlanetRoleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPlanetRole extends EditRecord
{
    protected static string $resource = PlanetRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
