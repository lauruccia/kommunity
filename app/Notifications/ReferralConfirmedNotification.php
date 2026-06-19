<?php

namespace App\Notifications;

use App\Models\Referral;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Inviata al segnalatore quando l'admin valida il valore della consulenza:
 * il valore entra in classifica e contribuisce ai premi.
 */
class ReferralConfirmedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Referral $referral,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'web_push'];
    }

    private function amount(): string
    {
        return number_format((float) ($this->referral->approved_value ?? $this->referral->declared_value ?? 0), 2, ',', '.').' €';
    }

    public function toWebPush(object $notifiable): array
    {
        return [
            'title' => __('push.referral_confirmed_title'),
            'body'  => __('push.referral_confirmed_body', ['amount' => $this->amount()]),
            'url'   => route('referrals.index', ['tab' => 'classifica']),
            'tag'   => 'referral-confirmed-'.$this->referral->id,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('push.referral_confirmed_title'))
            ->line(__('push.referral_confirmed_body', ['amount' => $this->amount()]))
            ->action(__('referrals.tabs.leaderboard'), route('referrals.index', ['tab' => 'classifica']));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'  => 'referral_confirmed',
            'title' => __('push.referral_confirmed_title'),
            'body'  => __('push.referral_confirmed_body', ['amount' => $this->amount()]),
            'url'   => route('referrals.index', ['tab' => 'classifica']),
            'icon'  => '🏆',
        ];
    }
}
