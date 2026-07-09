<?php

namespace App\Notifications;

use App\Models\MembershipApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Avvisa gli admin (super-admin / admin-community) che è arrivata
 * una nuova candidatura di ammissione da approvare o rifiutare.
 *
 * Canali: email + campanella (database) + push PWA.
 */
class NewMembershipApplicationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly MembershipApplication $application,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'web_push'];
    }

    public function toWebPush(object $notifiable): array
    {
        return [
            'title' => __('push.membership_application_title'),
            'body'  => __('push.membership_application_body', ['name' => $this->application->name]),
            'url'   => '/admin/membership-applications',
            'tag'   => 'membership-application-' . $this->application->getKey(),
            'requireInteraction' => true,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $app      = $this->application;
        $adminUrl = url('/admin/membership-applications');

        $presenterLine = $app->presenter
            ? $app->presenter->name . ' (dalla sua card)'
            : ($app->referrer_name ?: '—');

        return (new MailMessage)
            ->subject('📥 Nuova candidatura Kommunity — ' . $app->name)
            ->greeting('Ciao ' . $notifiable->name . '!')
            ->line('È arrivata una nuova candidatura di ammissione a Kommunity:')
            ->line('• **Nome:** ' . $app->name)
            ->line('• **Email:** ' . $app->email)
            ->line('• **Telefono:** ' . $app->phone)
            ->line('• **Tipo:** ' . ($app->isCompany() ? 'Azienda' : 'Privato') . ($app->vat_number ? ' — P.IVA ' . $app->vat_number : ''))
            ->line('• **Professione/attività:** ' . ($app->profession ?: '—'))
            ->line('• **Provenienza:** ' . $app->sourceLabel())
            ->line('• **Presentato da:** ' . $presenterLine)
            ->line('• **Pianeta proposto:** ' . ($app->chapter?->name ?? '—'))
            ->action('Valuta la candidatura', $adminUrl)
            ->line('Dal pannello puoi approvare (anche cambiando Pianeta) o rifiutare la richiesta.')
            ->salutation('Il sistema Kommunity');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'           => 'membership_application',
            'title'          => 'Nuova candidatura: ' . $this->application->name,
            'body'           => 'Da valutare: ' . $this->application->sourceLabel()
                . ' → ' . ($this->application->chapter?->name ?? 'nessun Pianeta proposto'),
            'url'            => '/admin/membership-applications',
            'icon'           => '📥',
            'actor'          => $this->application->name,
            'application_id' => $this->application->getKey(),
        ];
    }
}
