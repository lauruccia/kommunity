<?php

namespace App\Notifications;

use App\Models\Referral;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReferralReceivedNotification extends Notification
{
    public function __construct(
        private readonly Referral $referral,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $sender = $this->referral->sender;
        $url    = route('referrals.index');

        return (new MailMessage)
            ->subject('Nuovo referral da ' . $sender->name)
            ->greeting('Ciao ' . $notifiable->name . '!')
            ->line($sender->name . ' ti ha inviato un referral su Kommunity.')
            ->line('**' . $this->referral->title . '**')
            ->line($this->referral->description)
            ->action('Vedi il referral', $url)
            ->salutation('Il team Kommunity');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'  => 'referral_received',
            'title' => 'Nuovo referral da ' . $this->referral->sender->name,
            'body'  => mb_strimwidth($this->referral->title, 0, 80, '…'),
            'url'   => route('referrals.index'),
            'icon'  => '🔗',
            'actor' => $this->referral->sender->name,
        ];
    }
}
