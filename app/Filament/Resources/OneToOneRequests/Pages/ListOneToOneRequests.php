<?php

namespace App\Filament\Resources\OneToOneRequests\Pages;

use App\Filament\Resources\OneToOneRequests\OneToOneRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOneToOneRequests extends ListRecords
{
    protected static string $resource = OneToOneRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
