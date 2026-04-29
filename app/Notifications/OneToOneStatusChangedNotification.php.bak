<?php

namespace App\Notifications;

use App\Enums\OneToOneStatus;
use App\Models\OneToOneRequest;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OneToOneStatusChangedNotification extends Notification
{
    public function __construct(
        private readonly OneToOneRequest $oneToOneRequest,
        private readonly OneToOneStatus $newStatus,
        private readonly string $actorName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url   = route('one-to-ones.index');
        $label = $this->newStatus->label();

        $lines = match ($this->newStatus) {
            OneToOneStatus::Accepted    => $this->actorName . ' ha **accettato** la tua richiesta di incontro One-to-One.',
            OneToOneStatus::Declined    => $this->actorName . ' ha **rifiutato** la tua richiesta di incontro One-to-One.',
            OneToOneStatus::Rescheduled => $this->actorName . ' ha proposto di **riprogrammare** l\'incontro One-to-One.',
            OneToOneStatus::Cancelled   => $this->actorName . ' ha **annullato** l\'incontro One-to-One.',
            OneToOneStatus::Completed   => 'L\'incontro One-to-One con ' . $this->actorName . ' è stato **completato**.',
            default                     => $this->actorName . ' ha aggiornato lo stato del tuo One-to-One.',
        };

        return (new MailMessage)
            ->subject('One-to-One ' . $label . ' — ' . $this->actorName)
            ->greeting('Ciao ' . $notifiable->name . '!')
            ->line($lines)
            ->action('Vai ai tuoi One-to-One', $url)
            ->salutation('Il team Kommunity');
    }

    public function toArray(object $notifiable): array
    {
        $label = $this->newStatus->label();

        return [
            'type'  => 'one_to_one_status_changed',
            'title' => 'One-to-One ' . $label . ' da ' . $this->actorName,
            'body'  => mb_strimwidth($this->oneToOneRequest->goal, 0, 80, '…'),
            'url'   => route('one-to-ones.index'),
            'icon'  => match ($this->newStatus) {
                OneToOneStatus::Accepted  => '✅',
                OneToOneStatus::Declined  => '❌',
                OneToOneStatus::Cancelled => '🚫',
                OneToOneStatus::Completed => '🏆',
                default                   => '🔄',
            },
            'actor' => $this->actorName,
        ];
    }
}
