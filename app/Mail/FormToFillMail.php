<?php

namespace App\Mail;

use App\Helpers\EmailLayoutHelper;
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
        // Renderiza a view e aplica o layout
        $html = EmailLayoutHelper::renderViewContent('emails.form_link', [
            'patient' => $this->patient,
            'appointment' => $this->appointment,
            'url' => $this->url,
        ]);

        return $this->subject("Formulário Pré-Consulta")
            ->html($html);
    }
}

