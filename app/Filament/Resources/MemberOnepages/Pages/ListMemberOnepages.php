<?php

namespace App\Filament\Resources\MemberOnepages\Pages;

use App\Filament\Resources\MemberOnepages\MemberOnepageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMemberOnepages extends ListRecords
{
    protected static string $resource = MemberOnepageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nuova pagina'),
        ];
    }
}
