<?php

namespace App\Notifications;

use App\Models\Referral;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Inviata al CLIENTE segnalato quando il segnalatore lo collega a un professionista.
 * "Francesco ti ha consigliato a <professionista>."
 */
class ReferralClientReferredNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Referral $referral,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'web_push'];
    }

    private function body(): string
    {
        return __('push.referral_client_referred_body', [
            'sender' => $this->referral->sender?->name ?? '',
            'pro'    => $this->referral->recipient?->name ?? '',
        ]);
    }

    public function toWebPush(object $notifiable): array
    {
        return [
            'title' => __('push.referral_client_referred_title'),
            'body'  => $this->body(),
            'url'   => route('referrals.index', ['tab' => 'segnalato']),
            'tag'   => 'referral-client-'.$this->referral->id,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('push.referral_client_referred_title'))
            ->greeting('Ciao '.$notifiable->name.'!')
            ->line($this->body())
            ->line('**'.$this->referral->title.'**')
            ->action(__('referrals.tabs.client'), route('referrals.index', ['tab' => 'segnalato']));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'  => 'referral_client_referred',
            'title' => __('push.referral_client_referred_title'),
            'body'  => $this->body(),
            'url'   => route('referrals.index', ['tab' => 'segnalato']),
            'icon'  => '🙌',
        ];
    }
}
