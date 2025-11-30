<?php

namespace App\Mail;

use App\Models\Tenant\Patient;
use App\Models\Tenant\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FormToFillMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Patient $patient,
        public Appointment $appointment,
        public string $url
    ) {}

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject("Formulário Pré-Consulta")
            ->view('emails.form_link');
    }
}

