<?php

namespace App\Notifications;

use App\Models\MemberSubscription;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionApprovedNotification extends Notification
{
    public function __construct(
        private readonly MemberSubscription $subscription,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'web_push'];
    }

    public function toWebPush(object $notifiable): array
    {
        return [
            'title' => '🎉 Abbonamento approvato',
            'body'  => 'Il tuo abbonamento ' . ($this->subscription->plan?->name ?? 'Kommunity') . ' è stato approvato.',
            'url'   => route('subscriptions.index'),
            'tag'   => 'subscription-' . $this->subscription->id,
            'requireInteraction' => true,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $plan    = $this->subscription->plan;
        $url     = route('subscriptions.index');
        $expires = $this->subscription->ends_at
            ? 'Il tuo abbonamento è valido fino al **' . $this->subscription->ends_at->format('d/m/Y') . '**.'
            : 'Il tuo abbonamento non ha una data di scadenza.';

        return (new MailMessage)
            ->subject('Il tuo abbonamento è stato approvato!')
            ->greeting('Ciao ' . $notifiable->name . '!')
            ->line('Ottima notizia! Il tuo abbonamento **' . ($plan?->name ?? 'Kommunity') . '** è stato approvato.')
            ->line($expires)
            ->action('Accedi a Kommunity', $url)
            ->line('Ora hai accesso completo a tutte le funzionalità della piattaforma.')
            ->salutation('Il team Kommunity');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'  => 'subscription_approved',
            'title' => 'Abbonamento approvato!',
            'body'  => 'Il tuo abbonamento ' . ($this->subscription->plan?->name ?? 'Kommunity') . ' è stato approvato.',
            'url'   => route('subscriptions.index'),
            'icon'  => '🎉',
            'actor' => 'Kommunity',
        ];
    }
}
