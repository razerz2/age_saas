<?php

namespace Tests\Browser\Support;

final class TenantTestContext
{
    public function __construct(
        public readonly string $baseUrl,
        public readonly string $slug,
        public readonly string $email,
        public readonly string $password
    ) {
    }

    public static function fromEnvironment(): self
    {
        return new self(
            baseUrl: self::normalizeBaseUrl((string) (env('DUSK_TENANT_BASE_URL') ?: env('APP_URL', 'http://127.0.0.1:8000'))),
            slug: trim((string) env('DUSK_TENANT_SLUG', 'clinica-teste')),
            email: trim((string) env('DUSK_TENANT_LOGIN_EMAIL', 'admin@clinica-teste.com')),
            password: (string) env('DUSK_TENANT_LOGIN_PASSWORD', 'k.{D5a,;F7g;')
        );
    }

    public function loginPath(): string
    {
        return sprintf('/customer/%s/login', $this->slug);
    }

    public function loginUrl(): string
    {
        return sprintf('%s%s', $this->baseUrl, $this->loginPath());
    }

    private static function normalizeBaseUrl(string $url): string
    {
        return rtrim(trim($url), '/');
    }
}
