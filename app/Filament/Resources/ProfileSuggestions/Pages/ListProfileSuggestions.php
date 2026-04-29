<?php

namespace App\Filament\Resources\ProfileSuggestions\Pages;

use App\Filament\Resources\ProfileSuggestions\ProfileSuggestionResource;
use Filament\Resources\Pages\ListRecords;

class ListProfileSuggestions extends ListRecords
{
    protected static string $resource = ProfileSuggestionResource::class;
}
