<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemberWelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $resetUrl,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Benvenuto in Kommunity — Imposta la tua password')
            ->greeting('Ciao ' . $notifiable->name . '!')
            ->line('Il tuo account Kommunity è stato creato dall\'amministratore.')
            ->line('Per accedere alla piattaforma devi prima impostare la tua password personale cliccando il pulsante qui sotto.')
            ->action('Imposta la tua password', $this->resetUrl)
            ->line('Il link è valido per **60 minuti**. Se non riesci ad accedere puoi richiederne uno nuovo dalla pagina di login.')
            ->line('Se non hai richiesto questo account, ignora questa email.')
            ->salutation('Il team Kommunity');
    }
}
