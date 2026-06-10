<?php

namespace App\Filament\Resources\ChapterJoinRequests\Pages;

use App\Filament\Resources\ChapterJoinRequests\ChapterJoinRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListChapterJoinRequests extends ListRecords
{
    protected static string $resource = ChapterJoinRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
