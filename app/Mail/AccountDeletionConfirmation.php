<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountDeletionConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public string $confirmationUrl)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mail.account_deletion_subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.account-deletion-confirmation',
            with: [
                'user' => $this->user,
                'confirmationUrl' => $this->confirmationUrl,
            ],
        );
    }
}
