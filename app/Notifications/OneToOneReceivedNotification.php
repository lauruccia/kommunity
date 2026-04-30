<?php

namespace App\Notifications;

use App\Models\OneToOneRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OneToOneReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly OneToOneRequest $oneToOneRequest,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'web_push'];
    }

    public function toWebPush(object $notifiable): array
    {
        $requester = $this->oneToOneRequest->requester;
        return [
            'title' => '🤝 Nuova richiesta 1:1',
            'body'  => 'Da ' . ($requester->name ?? 'un membro') . ' — ' . \Illuminate\Support\Str::limit($this->oneToOneRequest->goal, 80),
            'url'   => route('one-to-ones.index', ['request' => $this->oneToOneRequest->id]),
            'tag'   => 'one-to-one-' . $this->oneToOneRequest->id,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $requester = $this->oneToOneRequest->requester;
        $url       = route('one-to-ones.index', ['request' => $this->oneToOneRequest->id]);

        return (new MailMessage)
            ->subject('Nuova richiesta One-to-One da ' . $requester->name)
            ->greeting('Ciao ' . $notifiable->name . '!')
            ->line($requester->name . ' ti ha inviato una richiesta di incontro One-to-One.')
            ->line('**Obiettivo:** ' . $this->oneToOneRequest->goal)
            ->action('Vedi la richiesta', $url)
            ->line('Puoi accettarla, rifiutarla o proporre un orario alternativo dalla sezione One-to-One.')
            ->salutation('Il team Kommunity');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'  => 'one_to_one_received',
            'title' => 'Nuova richiesta One-to-One da ' . $this->oneToOneRequest->requester->name,
            'body'  => mb_strimwidth($this->oneToOneRequest->goal, 0, 80, '…'),
            'url'   => route('one-to-ones.index', ['request' => $this->oneToOneRequest->id]),
            'icon'  => '🤝',
            'actor' => $this->oneToOneRequest->requester->name,
        ];
    }
}
