<?php

namespace App\Filament\Resources\PlanetRoles\Pages;

use App\Filament\Resources\PlanetRoles\PlanetRoleResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePlanetRole extends CreateRecord
{
    protected static string $resource = PlanetRoleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
