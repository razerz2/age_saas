<?php

if (!function_exists('mask_sensitive_data')) {
    /**
     * Mascara dados sensíveis em logs
     */
    function mask_sensitive_data($value, int $visibleChars = 4): string
    {
        if (empty($value) || strlen($value) <= $visibleChars) {
            return str_repeat('*', strlen($value) ?: 8);
        }

        $length = strlen($value);
        $visible = substr($value, 0, $visibleChars);
        $masked = str_repeat('*', max(0, $length - $visibleChars));

        return $visible . $masked;
    }
}

if (!function_exists('mask_token')) {
    /**
     * Mascara token completo
     */
    function mask_token(?string $token): string
    {
        if (empty($token)) {
            return '***';
        }

        if (strlen($token) <= 8) {
            return str_repeat('*', strlen($token));
        }

        return substr($token, 0, 4) . '***' . substr($token, -4);
    }
}

if (!function_exists('mask_url')) {
    /**
     * Mascara URL sensível (remove query params e paths)
     */
    function mask_url(?string $url): string
    {
        if (empty($url)) {
            return '***';
        }

        try {
            $parsed = parse_url($url);
            $scheme = $parsed['scheme'] ?? 'https';
            $host = $parsed['host'] ?? '';
            $path = isset($parsed['path']) ? '***' : '';

            return "{$scheme}://{$host}{$path}";
        } catch (\Exception $e) {
            return '***';
        }
    }
}

