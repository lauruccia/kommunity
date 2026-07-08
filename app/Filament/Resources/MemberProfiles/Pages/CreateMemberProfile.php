<?php

namespace App\Filament\Resources\MemberProfiles\Pages;

use App\Filament\Resources\MemberProfiles\MemberProfileResource;
use App\Models\Profession;
use Filament\Resources\Pages\CreateRecord;

class CreateMemberProfile extends CreateRecord
{
    protected static string $resource = MemberProfileResource::class;

    /**
     * professions (M2M) è dehydrated(false) nel form: va sincronizzato
     * manualmente dopo la creazione, espandendo i padri gerarchici
     * come fa ProfileController lato utente.
     */
    protected function afterCreate(): void
    {
        $professions = $this->data['professions'] ?? null;

        if ($professions !== null) {
            $this->getRecord()->professions()->sync(
                Profession::expandWithAncestors((array) $professions)
            );
        }
    }
}
