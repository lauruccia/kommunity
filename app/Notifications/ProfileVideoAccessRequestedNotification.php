<?php

namespace App\Notifications;

use App\Models\ProfileVideoAccessRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProfileVideoAccessRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly ProfileVideoAccessRequest $accessRequest,
        private readonly User $requester,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'web_push'];
    }

    public function toWebPush(object $notifiable): array
    {
        return [
            'title' => 'Richiesta video profilo',
            'body' => $this->requester->name . ' chiede di scambiare la videopresentazione.',
            'url' => $this->profileUrl(),
            'tag' => 'profile-video-access-' . $this->accessRequest->id,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Richiesta accesso alla videopresentazione')
            ->greeting('Ciao ' . $notifiable->name . '!')
            ->line($this->requester->name . ' ti chiede di scambiare la videopresentazione su Kommunity.')
            ->line('Se accetti, entrambi potrete vedere i rispettivi video profilo.')
            ->action('Gestisci richiesta', $this->profileUrl())
            ->salutation('Il team Kommunity');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'profile_video_access_requested',
            'title' => 'Richiesta videopresentazione',
            'body' => $this->requester->name . ' chiede di scambiare la videopresentazione.',
            'url' => $this->profileUrl(),
            'icon' => '▶',
            'actor' => $this->requester->name,
        ];
    }

    private function profileUrl(): string
    {
        $slug = $this->requester->memberOnepage?->slug;

        return $slug
            ? route('members.show', $slug)
            : route('directory.index');
    }
}
