<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Log;

class PhoneNormalizer
{
    public static function normalizeE164(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if ($digits === '') {
            return '';
        }

        if (!str_starts_with($digits, '55')) {
            $digits = '55' . $digits;
        }

        return $digits;
    }

    public static function normalizeWahaBrPhone(string $input): string
    {
        $raw = (string) $input;
        $digits = preg_replace('/\D+/', '', $raw);

        if ($digits === '') {
            throw new \InvalidArgumentException('Telefone inválido para WAHA');
        }

        if (!str_starts_with($digits, '55')) {
            $digits = '55' . $digits;
        }

        if (strlen($digits) === 13) {
            $afterDdd = substr($digits, 4, 1);
            if ($afterDdd === '9') {
                $digits = substr($digits, 0, 4) . substr($digits, 5);
            }
        }

        if (strlen($digits) !== 12) {
            throw new \InvalidArgumentException('Telefone inválido para WAHA');
        }

        if (config('app.debug')) {
            Log::debug('🔎 WAHA phone normalized', [
                'before' => self::maskPhone($raw),
                'after' => self::maskPhone($digits),
            ]);
        }

        return $digits;
    }

    public static function formatForWhatsAppBusiness(string $phone): string
    {
        $normalized = self::normalizeE164($phone);
        return $normalized === '' ? '' : '+' . $normalized;
    }

    public static function formatForZapi(string $phone): string
    {
        return self::normalizeE164($phone);
    }

    public static function formatForWahaChatId(string $phone): string
    {
        $trimmed = trim($phone);
        if ($trimmed === '') {
            return '';
        }

        if (str_contains($trimmed, '@')) {
            return $trimmed;
        }

        $normalized = self::normalizeWahaBrPhone($trimmed);
        return $normalized === '' ? '' : $normalized . '@c.us';
    }

    public static function maskPhone(string $input): string
    {
        $digits = preg_replace('/\D+/', '', (string) $input);
        $length = strlen($digits);
        if ($length === 0) {
            return '';
        }

        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        $prefix = substr($digits, 0, 2);
        $suffix = substr($digits, -4);
        $masked = str_repeat('*', max(0, $length - 6));

        return $prefix . $masked . $suffix;
    }
}
