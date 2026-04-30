<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Event $event,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'web_push'];
    }

    public function toWebPush(object $notifiable): array
    {
        return [
            'title' => '📅 Domani: ' . $this->event->title,
            'body'  => $this->event->starts_at->format('d/m/Y H:i') . ' · ' . ($this->event->location ?: 'Online'),
            'url'   => route('events.show', $this->event),
            'tag'   => 'event-reminder-' . $this->event->id,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url      = route('events.show', $this->event);
        $startsAt = $this->event->starts_at->format('d/m/Y \a\l\l\e H:i');
        $location = $this->event->location ?: 'Online';

        return (new MailMessage)
            ->subject('Promemoria: "' . $this->event->title . '" inizia domani')
            ->greeting('Ciao ' . $notifiable->name . '!')
            ->line('Ti ricordiamo che domani si tiene un evento a cui sei iscritto.')
            ->line('**' . $this->event->title . '**')
            ->line('📅 ' . $startsAt)
            ->line('📍 ' . $location)
            ->action('Vedi i dettagli', $url)
            ->line('A domani!')
            ->salutation('Il team Kommunity');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'  => 'event_reminder',
            'title' => 'Domani: ' . $this->event->title,
            'body'  => $this->event->starts_at->format('d/m/Y H:i') . ' · ' . ($this->event->location ?: 'Online'),
            'url'   => route('events.show', $this->event),
            'icon'  => '📅',
            'actor' => 'Kommunity',
        ];
    }
}
