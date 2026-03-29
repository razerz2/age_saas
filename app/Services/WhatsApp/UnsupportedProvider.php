<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Log;

class UnsupportedProvider implements WhatsAppProviderInterface
{
    public function __construct(
        private readonly string $providerKey
    ) {
    }

    public function sendMessage(string $phone, string $message): bool
    {
        Log::warning('Blocked WhatsApp send due to unsupported runtime provider', [
            'provider' => $this->providerKey,
            'phone' => PhoneNormalizer::maskPhone($phone),
        ]);

        return false;
    }

    public function formatPhone(string $phone): string
    {
        return PhoneNormalizer::normalizeE164($phone);
    }
}
