<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class TenantBranding
{
    private static ?array $cache = null;

    public static function resolve(bool $refresh = false): array
    {
        if (! $refresh && self::$cache !== null) {
            return self::$cache;
        }

        $tenantLogoLight = (string) self::tenantSetting('appearance.logo_light', self::tenantSetting('appearance.logo', ''));
        $tenantLogoDark = (string) self::tenantSetting('appearance.logo_dark', '');
        $tenantLogoMiniLight = (string) self::tenantSetting('appearance.logo_mini_light', self::tenantSetting('appearance.logo_mini', ''));
        $tenantLogoMiniDark = (string) self::tenantSetting('appearance.logo_mini_dark', '');
        $tenantFavicon = (string) self::tenantSetting('appearance.favicon', '');

        $platformLogoLegacy = (string) self::sysConfig('tenant.default_logo', self::sysConfig('platform.default_logo', ''));
        $platformLogoLight = (string) self::sysConfig('tenant.default_logo_light', self::sysConfig('platform.default_logo_light', ''));
        $platformLogoDark = (string) self::sysConfig('tenant.default_logo_dark', self::sysConfig('platform.default_logo_dark', ''));

        $platformLogoMiniLegacy = (string) self::sysConfig('tenant.default_logo_mini', self::sysConfig('platform.default_logo_mini', ''));
        $platformLogoMiniLight = (string) self::sysConfig('tenant.default_logo_mini_light', self::sysConfig('platform.default_logo_mini_light', ''));
        $platformLogoMiniDark = (string) self::sysConfig('tenant.default_logo_mini_dark', self::sysConfig('platform.default_logo_mini_dark', ''));

        $platformFavicon = (string) self::sysConfig('tenant.default_favicon', self::sysConfig('platform.default_favicon', ''));

        $logoLightUrl = self::firstAvailableUrl(
            [$tenantLogoLight, $platformLogoLight, $platformLogoLegacy],
            asset('tailadmin/assets/images/logo/logo.svg')
        );

        $logoDarkUrl = self::firstAvailableUrl(
            [$tenantLogoDark, $platformLogoDark, $platformLogoLight, $platformLogoLegacy],
            asset('tailadmin/assets/images/logo/logo-dark.svg')
        );

        $logoMiniLightUrl = self::firstAvailableUrl(
            [$tenantLogoMiniLight, $platformLogoMiniLight, $platformLogoMiniLegacy],
            asset('tailadmin/assets/images/logo/logo-icon.svg')
        );

        $logoMiniDarkUrl = self::firstAvailableUrl(
            [$tenantLogoMiniDark, $platformLogoMiniDark, $platformLogoMiniLight, $platformLogoMiniLegacy],
            asset('tailadmin/assets/images/logo/logo-icon.svg')
        );

        $faviconUrl = self::firstAvailableUrl(
            [$tenantFavicon, $platformFavicon],
            asset('favicon.ico')
        );

        self::$cache = [
            'logo_light_url' => $logoLightUrl,
            'logo_dark_url' => $logoDarkUrl,
            'logo_mini_light_url' => $logoMiniLightUrl,
            'logo_mini_dark_url' => $logoMiniDarkUrl,
            'favicon_url' => $faviconUrl,
        ];

        return self::$cache;
    }

    private static function firstAvailableUrl(array $candidates, string $fallback): string
    {
        foreach ($candidates as $candidate) {
            $resolved = self::resolveCandidateUrl($candidate);
            if ($resolved !== null && $resolved !== '') {
                return $resolved;
            }
        }

        return $fallback;
    }

    private static function resolveCandidateUrl(mixed $candidate): ?string
    {
        $value = trim((string) $candidate);
        if ($value === '') {
            return null;
        }

        if (preg_match('#^(https?:)?//#i', $value) === 1 || str_starts_with($value, 'data:')) {
            return $value;
        }

        if (str_starts_with($value, '/')) {
            return $value;
        }

        $publicPath = public_path($value);
        if (is_file($publicPath)) {
            return asset($value) . self::publicVersionQuery($publicPath);
        }

        try {
            if (Storage::disk('public')->exists($value)) {
                return Storage::url($value) . self::storageVersionQuery($value);
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }

    private static function storageVersionQuery(string $path): string
    {
        try {
            $lastModified = Storage::disk('public')->lastModified($path);
            return $lastModified ? '?v=' . $lastModified : '';
        } catch (\Throwable) {
            return '';
        }
    }

    private static function publicVersionQuery(string $path): string
    {
        $timestamp = @filemtime($path);
        return $timestamp ? '?v=' . $timestamp : '';
    }

    private static function tenantSetting(string $key, mixed $default = null): mixed
    {
        try {
            return tenant_setting($key, $default);
        } catch (\Throwable) {
            return $default;
        }
    }

    private static function sysConfig(string $key, mixed $default = null): mixed
    {
        try {
            return sysconfig($key, $default);
        } catch (\Throwable) {
            return $default;
        }
    }
}
