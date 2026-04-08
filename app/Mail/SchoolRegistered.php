<?php

namespace App\Mail;

use App\Models\School;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SchoolRegistered extends Mailable
{
    use Queueable, SerializesModels;

    public string $userLocale;

    public function __construct(
        public School $school,
        public string $adminName,
        string $recipientLocale = 'en'
    ) {
        $this->userLocale = $recipientLocale;
        $this->locale($recipientLocale);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mail.school_registered_subject', [], $this->userLocale),
        );
    }

    public function build()
    {
        return $this->view('emails.school-registered', [
            'school' => $this->school,
            'adminName' => $this->adminName,
            'locale' => $this->userLocale,
        ]);
    }
}
