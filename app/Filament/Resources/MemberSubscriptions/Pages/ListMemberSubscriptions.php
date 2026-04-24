<?php

namespace App\Filament\Resources\MemberSubscriptions\Pages;

use App\Filament\Resources\MemberSubscriptions\MemberSubscriptionResource;
use App\Enums\SubscriptionStatus;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;

class ListMemberSubscriptions extends ListRecords
{
    protected static string $resource = MemberSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }

    public function getTabs(): array
    {
        return [
            'all'      => Tab::make('Tutti'),
            'pending'  => Tab::make('In attesa')
                ->modifyQueryUsing(fn ($query) => $query->where('status', SubscriptionStatus::Pending->value))
                ->badge(\App\Models\MemberSubscription::where('status', SubscriptionStatus::Pending->value)->count()),
            'active'   => Tab::make('Attivi')
                ->modifyQueryUsing(fn ($query) => $query->whereIn('status', [
                    SubscriptionStatus::Active->value,
                    SubscriptionStatus::Trial->value,
                ])),
            'expired'  => Tab::make('Scaduti/Annullati')
                ->modifyQueryUsing(fn ($query) => $query->whereIn('status', [
                    SubscriptionStatus::Expired->value,
                    SubscriptionStatus::Cancelled->value,
                    SubscriptionStatus::Rejected->value,
                ])),
        ];
    }
}
