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

    // ── Helpers ───────────────────────────────────────────────────────────────

    protected function isParticipant(User $user, Referral $referral): bool
    {
        return in_array($user->id, [$referral->sender_id, $referral->recipient_id], true);
    }
}
