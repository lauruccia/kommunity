<?php

namespace App\Notifications;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMessageNotification extends Notification
{
    public function __construct(
        private readonly Conversation $conversation,
        private readonly User $sender,
        private readonly string $messagePreview,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url     = route('conversations.show', $this->conversation);
        $preview = mb_strimwidth($this->messagePreview, 0, 120, '…');

        return (new MailMessage)
            ->subject('Nuovo messaggio da ' . $this->sender->name)
            ->greeting('Ciao ' . $notifiable->name . '!')
            ->line($this->sender->name . ' ti ha inviato un messaggio su Kommunity.')
            ->line('"' . $preview . '"')
            ->action('Leggi e rispondi', $url)
            ->salutation('Il team Kommunity');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'new_message',
            'title'      => 'Nuovo messaggio da ' . $this->sender->name,
            'body'       => mb_strimwidth($this->messagePreview, 0, 80, '…'),
            'url'        => route('conversations.show', $this->conversation),
            'icon'       => '💬',
            'actor'      => $this->sender->name,
        ];
    }
}
