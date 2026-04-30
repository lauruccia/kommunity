<?php

namespace App\Notifications;

use App\Models\ForumPost;
use App\Models\ForumThread;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ForumReplyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly ForumThread $thread,
        private readonly ForumPost $post,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $replier = $this->post->user;
        $url     = route('forum.show', $this->thread);
        $preview = mb_strimwidth(strip_tags($this->post->content), 0, 120, '…');

        return (new MailMessage)
            ->subject($replier->name . ' ha risposto nel forum: ' . $this->thread->title)
            ->greeting('Ciao ' . $notifiable->name . '!')
            ->line($replier->name . ' ha risposto alla tua discussione **"' . $this->thread->title . '"**.')
            ->line('"' . $preview . '"')
            ->action('Leggi la risposta', $url)
            ->salutation('Il team Kommunity');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'  => 'forum_reply',
            'title' => $this->post->user->name . ' ha risposto nel forum',
            'body'  => mb_strimwidth($this->thread->title, 0, 80, '…'),
            'url'   => route('forum.show', $this->thread),
            'icon'  => '💬',
            'actor' => $this->post->user->name,
        ];
    }
}
