<?php

namespace App\Policies;

use App\Models\Referral;
use App\Models\User;

class ReferralPolicy
{
    /**
     * Vedere/agire su un referral: solo sender o recipient.
     */
    public function view(User $user, Referral $referral): bool
    {
        return $this->isParticipant($user, $referral);
    }

    /**
     * Aggiornare lo stato di un referral: solo sender o recipient.
     */
    public function updateStatus(User $user, Referral $referral): bool
    {
        return $this->isParticipant($user, $referral);
    }

    /**
     * Eliminare un referral: solo admin.
     */
    public function destroy(User $user, Referral $referral): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Prendere in carico (acknowledge): solo destinatario.
     */
    public function acknowledge(User $user, Referral $referral): bool
    {
        return $user->id === $referral->recipient_id;
    }

    /**
     * Rendere pubblica/privata una referenza: solo destinatario.
     */
    public function togglePublic(User $user, Referral $referral): bool
    {
        return $user->id === $referral->recipient_id;
    }

    /**
     * Vedere tutte le referenze (pannello moderazione): solo admin.
     */
    public function viewAll(User $user): bool
    {
        return $this->isAdmin($user);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    protected function isParticipant(User $user, Referral $referral): bool
    {
        return in_array($user->id, [$referral->sender_id, $referral->recipient_id], true);
    }

    protected function isAdmin(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin-community']);
    }
}
