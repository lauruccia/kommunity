<?php

namespace App\Filament\Resources\PlanetRoles\Pages;

use App\Filament\Resources\PlanetRoles\PlanetRoleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPlanetRoles extends ListRecords
{
    protected static string $resource = PlanetRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Nuovo ruolo')];
    }
}
