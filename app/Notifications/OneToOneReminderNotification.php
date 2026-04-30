<?php

namespace App\Notifications;

use App\Models\OneToOneRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Promemoria 1:1 — inviato sia al requester sia al recipient.
 * Window: '24h' o '1h' prima dell'incontro confermato.
 *
 * Richiede feature flag: reminders_one_to_one
 */
class OneToOneReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  string  $window  '24h' | '1h'
     */
    public function __construct(
        public readonly OneToOneRequest $request,
        public readonly string $window,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $request = $this->request;
        $url     = route('one-to-ones.index', ['request' => $request->id]);
        $when    = $request->requested_at?->locale('it')->isoFormat('dddd D MMMM, HH:mm') ?? '—';

        // L'altra parte (rispetto al destinatario corrente)
        $other = $notifiable->id === $request->requester_id
            ? $request->recipient
            : $request->requester;

        $subject = $this->window === '1h'
            ? "⏰ 1h all'incontro 1:1 con {$other->name}"
            : "📅 Domani: 1:1 con {$other->name}";

        $opener  = $this->window === '1h'
            ? "Ti ricordiamo che fra un'ora hai un 1:1 con {$other->name}."
            : "Ti ricordiamo che domani hai un 1:1 con {$other->name}.";

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Ciao ' . $notifiable->name . '!')
            ->line($opener)
            ->line('• **Quando:** ' . $when)
            ->line('• **Modalità:** ' . ($request->meeting_mode === 'online' ? 'Online' : 'In presenza'));

        if ($request->meeting_link) {
            $mail->line('• **Link:** ' . $request->meeting_link);
        }
        if ($request->meeting_location) {
            $mail->line('• **Luogo:** ' . $request->meeting_location);
        }

        return $mail
            ->action('Apri il 1:1 su Kommunity', $url)
            ->line('A presto!')
            ->salutation('Il team Kommunity');
    }

    public function toArray(object $notifiable): array
    {
        $other = $notifiable->id === $this->request->requester_id
            ? $this->request->recipient
            : $this->request->requester;

        return [
            'type'   => 'one_to_one_reminder_' . $this->window,
            'title'  => $this->window === '1h'
                ? "1h all'incontro con {$other?->name}"
                : "Domani 1:1 con {$other?->name}",
            'body'   => $this->request->goal
                ? \Illuminate\Support\Str::limit($this->request->goal, 80)
                : 'Promemoria one-to-one',
            'url'    => route('one-to-ones.index', ['request' => $this->request->id]),
            'icon'   => $this->window === '1h' ? '⏰' : '📅',
            'window' => $this->window,
        ];
    }
}
