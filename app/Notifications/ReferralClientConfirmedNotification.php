<?php

namespace App\Notifications;

use App\Models\Referral;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Inviata al segnalatore e agli admin quando il cliente conferma di aver
 * ricevuto il servizio: la referenza è pronta per la validazione admin.
 */
class ReferralClientConfirmedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Referral $referral,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'web_push'];
    }

    private function body(): string
    {
        return __('push.referral_client_confirmed_body', [
            'client' => $this->referral->client?->name ?? '',
        ]);
    }

    public function toWebPush(object $notifiable): array
    {
        return [
            'title' => __('push.referral_client_confirmed_title'),
            'body'  => $this->body(),
            'url'   => route('referrals.index'),
            'tag'   => 'referral-clientconfirmed-'.$this->referral->id,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('push.referral_client_confirmed_title'))
            ->line($this->body())
            ->action(__('referrals.actions.view'), route('referrals.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'  => 'referral_client_confirmed',
            'title' => __('push.referral_client_confirmed_title'),
            'body'  => $this->body(),
            'url'   => route('referrals.index'),
            'icon'  => '✅',
        ];
    }
}
