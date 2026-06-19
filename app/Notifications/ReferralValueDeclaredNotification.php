<?php

namespace App\Notifications;

use App\Models\Referral;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Inviata al segnalatore e agli admin quando il professionista dichiara
 * il valore della consulenza realizzata (in attesa di validazione admin).
 */
class ReferralValueDeclaredNotification extends Notification
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
        return number_format((float) ($this->referral->declared_value ?? 0), 2, ',', '.').' €';
    }

    public function toWebPush(object $notifiable): array
    {
        return [
            'title' => __('push.referral_declared_title'),
            'body'  => __('push.referral_declared_body', [
                'pro'    => $this->referral->recipient?->name ?? '',
                'amount' => $this->amount(),
            ]),
            'url'   => route('referrals.index'),
            'tag'   => 'referral-declared-'.$this->referral->id,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('push.referral_declared_title'))
            ->line(__('push.referral_declared_body', [
                'pro'    => $this->referral->recipient?->name ?? '',
                'amount' => $this->amount(),
            ]))
            ->action(__('referrals.actions.view'), route('referrals.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'  => 'referral_value_declared',
            'title' => __('push.referral_declared_title'),
            'body'  => __('push.referral_declared_body', [
                'pro'    => $this->referral->recipient?->name ?? '',
                'amount' => $this->amount(),
            ]),
            'url'   => route('referrals.index'),
            'icon'  => '💶',
        ];
    }
}
