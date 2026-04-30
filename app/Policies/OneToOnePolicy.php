<?php

namespace App\Policies;

use App\Enums\OneToOneStatus;
use App\Models\AvailabilitySlot;
use App\Models\OneToOneRequest;
use App\Models\User;

class OneToOnePolicy
{
    /**
     * Può vedere/agire sulla richiesta solo chi è requester o recipient.
     */
    public function view(User $user, OneToOneRequest $request): bool
    {
        return $this->isParticipant($user, $request);
    }

    /**
     * Aggiornamenti generici (status, note) → solo i partecipanti.
     */
    public function update(User $user, OneToOneRequest $request): bool
    {
        return $this->isParticipant($user, $request);
    }

    /**
     * Può confermare la chiusura solo se il modello lo consente
     * (canBeConfirmedBy()) e l'utente è partecipante.
     */
    public function confirm(User $user, OneToOneRequest $request): bool
    {
        if (! $this->isParticipant($user, $request)) {
            return false;
        }

        if (! method_exists($request, 'canBeConfirmedBy')) {
            return false;
        }

        return (bool) $request->canBeConfirmedBy($user->id);
    }

    /**
     * Può aggiornare lo stato (Accepted/Declined/Rescheduled) solo il
     * recipient e solo se la richiesta è ancora Pending o Rescheduled.
     */
    public function changeStatus(User $user, OneToOneRequest $request): bool
    {
        if ($request->recipient_id !== $user->id) {
            return false;
        }

        return in_array(
            $request->status,
            [OneToOneStatus::Pending, OneToOneStatus::Rescheduled],
            true
        );
    }

    /**
     * Può cancellare la richiesta solo il requester e solo se non è ancora
     * stata completata.
     */
    public function cancel(User $user, OneToOneRequest $request): bool
    {
        if ($request->requester_id !== $user->id) {
            return false;
        }

        return $request->status !== OneToOneStatus::Completed;
    }

    /**
     * Può eliminare uno slot di disponibilità solo il proprietario.
     */
    public function deleteSlot(User $user, AvailabilitySlot $slot): bool
    {
        return $slot->user_id === $user->id;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    protected function isParticipant(User $user, OneToOneRequest $request): bool
    {
        return in_array($user->id, [$request->requester_id, $request->recipient_id], true);
    }
}
