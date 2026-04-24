<?php

namespace App\Filament\Resources\MemberSubscriptions\Pages;

use App\Filament\Resources\MemberSubscriptions\MemberSubscriptionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMemberSubscription extends CreateRecord
{
    protected static string $resource = MemberSubscriptionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['requested_at'] = $data['requested_at'] ?? now();
        $data['approved_by']  = auth()->id();
        $data['approved_at']  = now();
        return $data;
    }
}
