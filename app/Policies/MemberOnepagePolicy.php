<?php

namespace App\Policies;

use App\Models\MemberOnepage;
use App\Models\User;

class MemberOnepagePolicy
{
    /**
     * La onepage è pubblica fra membri attivi: chiunque sia loggato e abbia
     * accesso alla directory può vederla.
     */
    public function view(User $user, MemberOnepage $onepage): bool
    {
        if (! (bool) $onepage->is_active) {
            // Se non è attiva, solo il proprietario o un admin può vederla
            return $user->id === $onepage->user_id
                || $user->hasAnyRole(['super-admin', 'admin-community']);
        }

        return true;
    }

    /**
     * Aggiornare la propria onepage: solo il proprietario.
     */
    public function update(User $user, MemberOnepage $onepage): bool
    {
        return $user->id === $onepage->user_id;
    }

    /**
     * Eliminare la onepage: proprietario o admin.
     */
    public function delete(User $user, MemberOnepage $onepage): bool
    {
        return $user->id === $onepage->user_id
            || $user->hasAnyRole(['super-admin', 'admin-community']);
    }
}
