<?php

namespace App\Mail;

use App\Models\School;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LicenseSeatsAdded extends Mailable
{
    use Queueable, SerializesModels;

    public string $userLocale;

    public function __construct(
        public School $school,
        public int $addedSeats,
        public int $newTotal,
        public int $usedSeats,
        string $recipientLocale = 'en'
    ) {
        $this->userLocale = $recipientLocale;
        $this->locale($recipientLocale);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mail.seats_added_subject', [], $this->userLocale),
        );
    }

    public function build()
    {
        return $this->view('emails.license-seats-added', [
            'school' => $this->school,
            'addedSeats' => $this->addedSeats,
            'newTotal' => $this->newTotal,
            'usedSeats' => $this->usedSeats,
            'locale' => $this->userLocale,
        ]);
    }
}
