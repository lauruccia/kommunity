<?php

namespace App\Mail;

use App\Models\MembershipApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Conferma al candidato che la sua candidatura è stata ricevuta
 * ed è in valutazione. Inviata nella lingua del candidato (it/en)
 * tramite ->locale() dal controller.
 */
class MembershipApplicationReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly MembershipApplication $application,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('application.mail_received_subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.membership-received',
            with: [
                'application' => $this->application,
                'presenter'   => $this->application->presenter,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
