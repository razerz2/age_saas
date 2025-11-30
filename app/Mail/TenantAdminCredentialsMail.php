<?php

namespace App\Mail;

use App\Models\Platform\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantAdminCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $tenant;
    public $loginUrl;
    public $adminEmail;
    public $adminPassword;

    /**
     * Create a new message instance.
     */
    public function __construct(Tenant $tenant, string $loginUrl, string $adminEmail, string $adminPassword)
    {
        $this->tenant = $tenant;
        $this->loginUrl = $loginUrl;
        $this->adminEmail = $adminEmail;
        $this->adminPassword = $adminPassword;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Credenciais de acesso ao seu painel da clínica',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.tenant-admin-credentials',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
