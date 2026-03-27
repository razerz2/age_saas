<?php

namespace App\Services\Tenant\WhatsAppBot\Provider;

use App\Services\Tenant\WhatsAppBot\Provider\Contracts\WhatsAppBotProviderAdapterInterface;
use App\Services\WhatsApp\PhoneNormalizer;

abstract class AbstractWhatsAppBotProviderAdapter implements WhatsAppBotProviderAdapterInterface
{
    protected function normalizePhone(?string $value): string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return '';
        }

        if (str_contains($raw, '@')) {
            $raw = (string) strtok($raw, '@');
        }

        $digits = preg_replace('/\D+/', '', $raw) ?? '';
        if ($digits === '') {
            return '';
        }

        return PhoneNormalizer::normalizeE164($digits);
    }

    /**
     * @param array<int, mixed> $candidates
     */
    protected function firstNonEmptyString(array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            $value = trim((string) $candidate);
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    protected function normalizeOutboundText(string $text): string
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", $text);
        $normalized = trim($normalized);

        if ($normalized === '') {
            return '';
        }

        if (strlen($normalized) > 4096) {
            return substr($normalized, 0, 4096);
        }

        return $normalized;
    }
}
