<?php

namespace App\Support;

use App\Services\WhatsApp\PhoneNormalizer;

class PlatformTwoFactorPhoneResolver
{
    /**
     * @return array{phone: ?string, reason: ?string}
     */
    public function resolveWithReason(object $user): array
    {
        $rawPhone = $this->extractRawPhone($user);
        if ($rawPhone === null) {
            return [
                'phone' => null,
                'reason' => 'missing_phone_field_or_value',
            ];
        }

        $normalized = PhoneNormalizer::normalizeE164($rawPhone);
        $length = strlen($normalized);
        if ($normalized === '' || $length < 12 || $length > 13) {
            return [
                'phone' => null,
                'reason' => 'invalid_phone_format',
            ];
        }

        return [
            'phone' => $normalized,
            'reason' => null,
        ];
    }

    public function resolve(object $user): ?string
    {
        return $this->resolveWithReason($user)['phone'];
    }

    private function extractRawPhone(object $user): ?string
    {
        $candidates = [
            data_get($user, 'phone'),
            data_get($user, 'telefone'),
        ];

        foreach ($candidates as $candidate) {
            if (!is_string($candidate) && !is_numeric($candidate)) {
                continue;
            }

            $value = trim((string) $candidate);
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }
}
