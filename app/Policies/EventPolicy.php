<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    /**
     * Determina se l'utente può visualizzare un evento (deve essere pubblicato,
     * salvo che ne sia il manager).
     */
    public function view(User $user, Event $event): bool
    {
        if ((bool) $event->is_published) {
            return true;
        }

        return $this->canManage($user, $event);
    }

    /**
     * Può creare eventi: super-admin, admin-community, leader-capitolo (con
     * almeno un capitolo gestito) oppure permesso esplicito "gestire-eventi".
     */
    public function create(User $user): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        if ($user->can('gestire-eventi')) {
            return true;
        }

        if ($user->hasRole('leader-capitolo')) {
            return ! empty($this->managedChapterIds($user));
        }

        return false;
    }

    /**
     * Può gestire (modificare/cancellare/invitare) un evento specifico.
     */
    public function manage(User $user, Event $event): bool
    {
        return $this->canManage($user, $event);
    }

    /**
     * Può inviare inviti per l'evento: stessa logica di manage.
     */
    public function invite(User $user, Event $event): bool
    {
        return $this->canManage($user, $event);
    }

    /**
     * Può annullare l'evento.
     */
    public function cancel(User $user, Event $event): bool
    {
        return $this->canManage($user, $event);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    protected function canManage(User $user, Event $event): bool
    {
        if ($this->isAdmin($user) || $user->can('gestire-eventi')) {
            return true;
        }

        return in_array($event->chapter_id, $this->managedChapterIds($user), true);
    }

    protected function isAdmin(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin-community']);
    }

    /**
     * Capitoli gestiti da un leader. La logica è la stessa di
     * EventController::managedChapterIds() — accetta sia la relazione
     * `managedChapters` sul modello User, sia l'attributo `managed_chapter_ids`
     * (per compat con vecchie implementazioni).
     */
    protected function managedChapterIds(User $user): array
    {
        if (method_exists($user, 'managedChapters')) {
            try {
                return $user->managedChapters()->pluck('chapters.id')->all();
            } catch (\Throwable) {
                // fallback sotto
            }
        }

        return is_array($user->managed_chapter_ids ?? null)
            ? $user->managed_chapter_ids
            : \App\Models\Chapter::query()->where('leader_id', $user->id)->pluck('id')->all();
    }
}
