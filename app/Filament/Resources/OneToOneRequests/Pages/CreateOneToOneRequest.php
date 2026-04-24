<?php

namespace App\Filament\Resources\OneToOneRequests\Pages;

use App\Filament\Resources\OneToOneRequests\OneToOneRequestResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOneToOneRequest extends CreateRecord
{
    protected static string $resource = OneToOneRequestResource::class;
}
