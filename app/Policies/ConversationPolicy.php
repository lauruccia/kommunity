<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    /**
     * Visualizzare una conversazione: solo se l'utente è partecipante.
     */
    public function view(User $user, Conversation $conversation): bool
    {
        return $this->isParticipant($user, $conversation);
    }

    /**
     * Inviare un messaggio: solo se partecipante.
     */
    public function sendMessage(User $user, Conversation $conversation): bool
    {
        return $this->isParticipant($user, $conversation);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    protected function isParticipant(User $user, Conversation $conversation): bool
    {
        return $conversation->participants()
            ->where('users.id', $user->id)
            ->exists();
    }
}
