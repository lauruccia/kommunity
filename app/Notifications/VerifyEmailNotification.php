<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail
{
    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->subject('Attiva il tuo account Kommunity')
            ->greeting('Benvenuto in Kommunity')
            ->line('Conferma il tuo indirizzo email per attivare l\'account e accedere alla tua area utente.')
            ->action('Attiva il mio account', $url)
            ->line('Il link è valido per 24 ore. Se scade, puoi richiederne uno nuovo dalla pagina di accesso.')
            ->line('Se non hai creato tu questo account, puoi ignorare questa email.')
            ->salutation('Il team Kommunity');
    }
}
