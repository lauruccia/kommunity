<?php

namespace App\Notifications;

use App\Models\Event;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventInvitationNotification extends Notification
{
    public function __construct(
        private readonly Event $event,
        private readonly User $inviter,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $dateLabel = $this->event->starts_at->translatedFormat('l d F Y') . ' alle ' . $this->event->starts_at->format('H:i');
        $location  = $this->event->location ?: 'Online';

        return (new MailMessage)
            ->subject('Sei invitato: ' . $this->event->title)
            ->greeting('Ciao ' . $notifiable->name . '!')
            ->line($this->inviter->name . ' ti ha invitato all\'evento:')
            ->line('**' . $this->event->title . '**')
            ->line('📅 ' . $dateLabel)
            ->line('📍 ' . $location)
            ->action('Vedi evento e rispondi', route('events.show', $this->event))
            ->line('Accedi alla piattaforma Kommunity per confermare la tua partecipazione.')
            ->salutation('Il team Kommunity');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'         => 'event_invitation',
            'event_id'     => $this->event->id,
            'event_title'  => $this->event->title,
            'event_url'    => route('events.show', $this->event),
            'inviter_name' => $this->inviter->name,
            'starts_at'    => $this->event->starts_at->toIso8601String(),
            'location'     => $this->event->location ?: 'Online',
        ];
    }
}
