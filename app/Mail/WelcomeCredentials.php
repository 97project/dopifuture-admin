<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeCredentials extends Mailable
{
    use Queueable, SerializesModels;

    public string $userLocale;

    public function __construct(
        public User $user,
        public string $plainPassword
    ) {
        $this->userLocale = $user->locale ?? 'en';
        $this->locale($this->userLocale);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mail.welcome_subject', [], $this->userLocale),
        );
    }

    public function build()
    {
        return $this->view('emails.welcome-credentials', [
            'user' => $this->user,
            'plainPassword' => $this->plainPassword,
            'loginUrl' => 'https://dopifuture.97.team/login',
            'locale' => $this->userLocale,
        ]);
    }
}
