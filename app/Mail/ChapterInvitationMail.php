<?php

namespace App\Mail;

use App\Models\ChapterInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ChapterInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly ChapterInvitation $invitation,
    ) {}

    public function envelope(): Envelope
    {
        $planetName = $this->invitation->chapter?->name ?? 'un Pianeta';

        return new Envelope(
            subject: 'Sei stato invitato a entrare in ' . $planetName . ' — Kommunity',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.chapter-invitation',
            with: [
                'invitation'  => $this->invitation,
                'chapter'     => $this->invitation->chapter,
                'invitedBy'   => $this->invitation->invitedBy,
                'inviteUrl'   => $this->invitation->inviteUrl(),
                'expiresAt'   => $this->invitation->expires_at,
                'message'     => $this->invitation->message,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
