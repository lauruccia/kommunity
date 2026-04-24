<?php

namespace App\Filament\Resources\MemberOnepages\Pages;

use App\Filament\Resources\MemberOnepages\MemberOnepageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMemberOnepage extends EditRecord
{
    protected static string $resource = MemberOnepageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
