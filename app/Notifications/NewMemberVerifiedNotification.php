<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifica agli admin che un nuovo membro ha confermato l'email
 * ed è stato auto-approvato nella directory.
 */
class NewMemberVerifiedNotification extends Notification implements ShouldQueue
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
        $member   = $this->newMember;
        $profile  = $member->memberProfile;
        $adminUrl = url('/admin/member-profiles/' . ($profile?->getKey() ?? '') . '/edit');

        return (new MailMessage)
            ->subject('✅ Nuovo membro attivo — ' . $member->name)
            ->greeting('Ciao ' . $notifiable->name . '!')
            ->line('Un nuovo membro ha confermato la propria email ed è stato **auto-approvato** nella directory Kommunity:')
            ->line('**Nome:** ' . $member->name)
            ->line('**Email:** ' . $member->email)
            ->line('**Invitato da:** ' . ($member->invitedBy?->name ?? $member->invited_by_name ?? '—'))
            ->line('**Pianeta:** ' . ($profile?->chapter?->name ?? '—'))
            ->line('Il profilo è ora visibile in directory con status **Attivo**.')
            ->action('Visualizza scheda membro', $adminUrl)
            ->line('Se necessario, puoi sospendere o modificare il profilo dal pannello admin.')
            ->salutation('Il sistema Kommunity');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'new_member_verified',
            'title'         => '✅ Nuovo membro attivo: ' . $this->newMember->name,
            'body'          => $this->newMember->name . ' ha verificato la email ed è ora visibile in directory.',
            'url'           => '/admin/member-profiles',
            'icon'          => '✅',
            'actor'         => $this->newMember->name,
            'new_member_id' => $this->newMember->getKey(),
        ];
    }
}
