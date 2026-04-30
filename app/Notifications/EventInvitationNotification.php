<?php

namespace App\Notifications;

use App\Models\Event;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Event $event,
        protected User  $inviter,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Sei stato invitato: ' . $this->event->title)
            ->greeting('Ciao ' . $notifiable->name . '!')
            ->line($this->inviter->name . ' ti ha invitato all\'evento: **' . $this->event->title . '**')
            ->line('📅 ' . $this->event->starts_at->translatedFormat('d F Y') . ' — ' . $this->event->starts_at->format('H:i'))
            ->line('📍 ' . ($this->event->location ?: 'Online'))
            ->action('Vedi l\'evento', route('events.show', $this->event))
            ->line('Puoi rispondere all\'invito direttamente dalla pagina dell\'evento.');
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

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
