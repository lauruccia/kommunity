<?php

namespace App\Filament\Resources\MemberOnepages\Pages;

use App\Filament\Resources\MemberOnepages\MemberOnepageResource;
use App\Models\MemberProfile;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMemberOnepage extends EditRecord
{
    protected static string $resource = MemberOnepageResource::class;

    /**
     * Viene chiamato prima di aggiornare il record MemberOnepage.
     * I campi "profile_*" appartengono a MemberProfile: li salviamo
     * separatamente e li rimuoviamo prima che Eloquent li veda.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $userId = $this->record?->user_id ?? $data['user_id'] ?? null;

        if ($userId) {
            $profileData = [];

            if (array_key_exists('profile_skills', $data)) {
                $profileData['skills'] = $data['profile_skills'] ?: null;
            }

            if (array_key_exists('profile_networking_goals', $data)) {
                $profileData['networking_goals'] = $data['profile_networking_goals'] ?: null;
            }

            if (! empty($profileData)) {
                MemberProfile::where('user_id', $userId)->update($profileData);
            }
        }

        // Rimuovi i campi virtuali: non esistono nella tabella member_onepages
        unset($data['profile_skills'], $data['profile_networking_goals']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
