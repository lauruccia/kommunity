<?php

namespace App\Mail;

use App\Models\ChapterInvitation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationAcceptedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly ChapterInvitation $invitation,
        public readonly User $newUser,
    ) {}

    public function envelope(): Envelope
    {
        $planetName = $this->invitation->chapter?->name ?? 'un Pianeta';

        return new Envelope(
            subject: $this->newUser->name . ' ha accettato il tuo invito a ' . $planetName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invitation-accepted',
            with: [
                'invitation' => $this->invitation,
                'chapter'    => $this->invitation->chapter,
                'newUser'    => $this->newUser,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
