<?php

namespace App\Filament\Resources\FeatureFlags\Pages;

use App\Filament\Resources\FeatureFlags\FeatureFlagResource;
use Filament\Resources\Pages\ListRecords;

class ListFeatureFlags extends ListRecords
{
    protected static string $resource = FeatureFlagResource::class;

    /**
     * Niente CreateAction: i feature flag vengono creati via seed/SQL.
     * L'admin può solo modificarli (toggle, settings, descrizione).
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
