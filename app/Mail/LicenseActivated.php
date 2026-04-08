<?php

namespace App\Mail;

use App\Models\License;
use App\Models\School;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LicenseActivated extends Mailable
{
    use Queueable, SerializesModels;

    public string $userLocale;

    public function __construct(
        public License $license,
        public School $school,
        string $recipientLocale = 'en'
    ) {
        $this->userLocale = $recipientLocale;
        $this->locale($recipientLocale);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mail.license_activated_subject', [], $this->userLocale),
        );
    }

    public function build()
    {
        return $this->view('emails.license-activated', [
            'license' => $this->license,
            'school' => $this->school,
            'locale' => $this->userLocale,
        ]);
    }
}
