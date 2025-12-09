<?php

namespace App\Mail;

use App\Models\Tenant\Patient;
use App\Models\Tenant\PatientLogin;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PatientLoginCredentials extends Mailable
{
    use Queueable, SerializesModels;

    public $patient;
    public $login;
    public $password;
    public $portalUrl;
    public $tenantName;

    /**
     * Create a new message instance.
     */
    public function __construct(Patient $patient, PatientLogin $login, string $password, string $portalUrl, string $tenantName)
    {
        $this->patient = $patient;
        $this->login = $login;
        $this->password = $password;
        $this->portalUrl = $portalUrl;
        $this->tenantName = $tenantName;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject("Acesso ao Portal do Paciente - {$this->tenantName}")
                    ->html(\App\Helpers\EmailLayoutHelper::renderViewContent('tenant.patients.emails.login-credentials', [
                        'patient' => $this->patient,
                        'login' => $this->login,
                        'password' => $this->password,
                        'portalUrl' => $this->portalUrl,
                        'tenantName' => $this->tenantName,
                    ]));
    }
}
