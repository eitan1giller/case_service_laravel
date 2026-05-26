<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CaseNotification extends Mailable
{
    use Queueable, SerializesModels;

    public array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function build()
    {
        $subject = $this->payload['subject'] ?? 'עדכון פניה';
        return $this->subject($subject)
            ->view('emails.case_notification')
            ->with(['payload' => $this->payload]);
    }
}
