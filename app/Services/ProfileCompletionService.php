<?php

namespace App\Services;

use App\Models\User;

class ProfileCompletionService
{
    /**
     * Ogni campo vale 10 punti → massimo 100%.
     * L'ordine è anche la priorità di visualizzazione nei suggerimenti.
     */
    private array $checks = [
        'avatar' => [
            'label'      => 'Carica la tua foto profilo',
            'icon'       => '📸',
            'test'       => 'hasAvatar',
        ],
        'company_name' => [
            'label'      => 'Aggiungi il nome della tua azienda o studio',
            'icon'       => '🏢',
            'test'       => 'hasCompanyName',
        ],
        'short_bio' => [
            'label'      => 'Scrivi una bio di presentazione',
            'icon'       => '✍️',
            'test'       => 'hasBio',
        ],
        'profession' => [
            'label'      => 'Seleziona la tua professione',
            'icon'       => '💼',
            'test'       => 'hasProfession',
        ],
        'city' => [
            'label'      => 'Indica la tua città',
            'icon'       => '📍',
            'test'       => 'hasCity',
        ],
        'phone' => [
            'label'      => 'Aggiungi un numero di telefono',
            'icon'       => '📞',
            'test'       => 'hasPhone',
        ],
        'online' => [
            'label'      => 'Aggiungi sito web o profilo LinkedIn',
            'icon'       => '🔗',
            'test'       => 'hasOnlinePresence',
        ],
        'services' => [
            'label'      => 'Descrivi i servizi che offri',
            'icon'       => '🛠',
            'test'       => 'hasServices',
        ],
        'networking_goals' => [
            'label'      => 'Indica cosa cerchi nella community',
            'icon'       => '🎯',
            'test'       => 'hasNetworkingGoals',
        ],
        'video' => [
            'label'      => 'Aggiungi un video di presentazione',
            'icon'       => '🎬',
            'test'       => 'hasVideo',
        ],
    ];

    public function calculate(User $user): array
    {
        $profile   = $user->memberProfile;
        $completed = [];
        $missing   = [];

        foreach ($this->checks as $key => $check) {
            $method = $check['test'];
            if ($this->$method($user, $profile)) {
                $completed[] = array_merge(['key' => $key], $check);
            } else {
                $missing[] = array_merge(['key' => $key], $check);
            }
        }

        $percentage = (int) round(count($completed) / count($this->checks) * 100);

        return [
            'percentage' => $percentage,
            'completed'  => $completed,
            'missing'    => $missing,
            'total'      => count($this->checks),
            'done'       => count($completed),
        ];
    }

    // ── Test singoli ─────────────────────────────────────────────────────────

    private function hasAvatar(User $user, $profile): bool
    {
        return $profile && filled($profile->avatar);
    }

    private function hasCompanyName(User $user, $profile): bool
    {
        return $profile && filled($profile->company_name);
    }

    private function hasBio(User $user, $profile): bool
    {
        return $profile && (filled($profile->short_bio) || filled($profile->bio));
    }

    private function hasProfession(User $user, $profile): bool
    {
        if (! $profile) {
            return false;
        }
        // Carica le professioni solo se non già caricate
        if (! $profile->relationLoaded('professions')) {
            $profile->load('professions');
        }

        return $profile->professions->isNotEmpty() || filled($profile->profession_id);
    }

    private function hasCity(User $user, $profile): bool
    {
        return $profile && filled($profile->city_id);
    }

    private function hasPhone(User $user, $profile): bool
    {
        return $profile && filled($profile->phone);
    }

    private function hasOnlinePresence(User $user, $profile): bool
    {
        return $profile && (filled($profile->website) || filled($profile->linkedin_url));
    }

    private function hasServices(User $user, $profile): bool
    {
        return $profile && filled($profile->services);
    }

    private function hasNetworkingGoals(User $user, $profile): bool
    {
        return $profile && filled($profile->networking_goals);
    }

    private function hasVideo(User $user, $profile): bool
    {
        return $profile && (filled($profile->intro_video) || filled($profile->intro_video_url));
    }
}
