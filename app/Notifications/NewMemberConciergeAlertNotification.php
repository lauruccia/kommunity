<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Avvisa gli admin (super-admin / admin-community) che un nuovo membro si è
 * appena registrato e va contattato in onboarding "concierge".
 *
 * Richiede che la feature flag `concierge_onboarding` sia attiva.
 * L'invio è schedulato in coda (ShouldQueue) per non rallentare il flow di
 * registrazione dell'utente.
 */
class NewMemberConciergeAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly User $newMember,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $newMember = $this->newMember;
        $adminUrl  = url('/admin/users/' . $newMember->getKey() . '/edit');

        return (new MailMessage)
            ->subject('🟢 Nuovo membro Kommunity — Concierge entro 24h')
            ->greeting('Ciao ' . $notifiable->name . '!')
            ->line('Si è appena registrato un nuovo membro su Kommunity:')
            ->line('• **Nome:** ' . $newMember->name)
            ->line('• **Email:** ' . $newMember->email)
            ->line('• **Capitolo:** ' . ($newMember->memberProfile?->chapter?->name ?? '—'))
            ->line('• **Invitato da:** ' . ($newMember->invitedBy?->name ?? $newMember->invited_by_name ?? '—'))
            ->line('Per massimizzare l\'attivazione, prendi contatto entro 24 ore con una chiamata o un breve Loom personalizzato.')
            ->action('Apri scheda membro', $adminUrl)
            ->line('Quando hai completato il contatto, segna l\'utente come "Concierge completato" dal pannello admin.')
            ->salutation('Il sistema Kommunity');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'         => 'new_member_concierge_alert',
            'title'        => 'Nuovo membro: ' . $this->newMember->name,
            'body'         => 'Da contattare entro 24h per l\'onboarding concierge.',
            'url'          => '/admin/users/' . $this->newMember->getKey() . '/edit',
            'icon'         => '🟢',
            'actor'        => $this->newMember->name,
            'new_member_id'=> $this->newMember->getKey(),
        ];
    }
}
