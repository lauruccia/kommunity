<?php

namespace App\Mail;

use App\Models\MembershipApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email di cortesia al candidato la cui candidatura non è stata accolta.
 * Invio opzionale (toggle nell'azione "Rifiuta" del pannello admin).
 */
class MembershipRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly MembershipApplication $application,
        public readonly ?string $reason = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('application.mail_rejected_subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.membership-rejected',
            with: [
                'application' => $this->application,
                'reason'      => $this->reason,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
