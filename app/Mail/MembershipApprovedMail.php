<?php

namespace App\Mail;

use App\Models\MembershipApplication;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email di benvenuto al candidato approvato: contiene il link
 * "imposta password" (token reset Laravel). Il token scade secondo
 * config('auth.passwords.users.expire'): l'email spiega come
 * rigenerarlo da "Password dimenticata" se scaduto.
 */
class MembershipApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly MembershipApplication $application,
        public readonly User $user,
        public readonly string $setPasswordUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('application.mail_approved_subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.membership-approved',
            with: [
                'application'    => $this->application,
                'user'           => $this->user,
                'setPasswordUrl' => $this->setPasswordUrl,
                'planet'         => $this->application->chapter,
                'expireMinutes'  => (int) config('auth.passwords.users.expire', 60),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
