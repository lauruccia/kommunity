<?php

namespace App\Filament\Resources\OneToOneRequests\Pages;

use App\Filament\Resources\OneToOneRequests\OneToOneRequestResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewOneToOneRequest extends ViewRecord
{
    protected static string $resource = OneToOneRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
