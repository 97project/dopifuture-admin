<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeCredentials extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $plainPassword
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'DopiFuture — Hesap Bilgileriniz',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.welcome-credentials',
            with: [
                'user' => $this->user,
                'plainPassword' => $this->plainPassword,
                'loginUrl' => config('app.url') . '/login',
            ],
        );
    }
}
