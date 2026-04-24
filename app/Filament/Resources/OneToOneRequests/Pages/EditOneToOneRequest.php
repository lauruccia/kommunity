<?php

namespace App\Filament\Resources\OneToOneRequests\Pages;

use App\Filament\Resources\OneToOneRequests\OneToOneRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditOneToOneRequest extends EditRecord
{
    protected static string $resource = OneToOneRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
