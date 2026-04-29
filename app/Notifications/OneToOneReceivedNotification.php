<?php

namespace App\Notifications;

use App\Models\OneToOneRequest;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OneToOneReceivedNotification extends Notification
{
    public function __construct(
        private readonly OneToOneRequest $oneToOneRequest,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $requester = $this->oneToOneRequest->requester;
        $url       = route('one-to-ones.index');

        return (new MailMessage)
            ->subject('Nuova richiesta One-to-One da ' . $requester->name)
            ->greeting('Ciao ' . $notifiable->name . '!')
            ->line($requester->name . ' ti ha inviato una richiesta di incontro One-to-One.')
            ->line('**Obiettivo:** ' . $this->oneToOneRequest->goal)
            ->action('Vedi la richiesta', $url)
            ->line('Puoi accettarla, rifiutarla o proporre un orario alternativo dalla sezione One-to-One.')
            ->salutation('Il team Kommunity');
    }
}
