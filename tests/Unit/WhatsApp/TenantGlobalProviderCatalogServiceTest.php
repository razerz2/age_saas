<?php

use App\Services\WhatsApp\TenantGlobalProviderCatalogService;
use Tests\TestCase;

uses(TestCase::class);

it('supports waha and evolution in tenant global provider catalog', function () {
    $service = new TenantGlobalProviderCatalogService();

    expect($service->supportedProviderOptions())->toBe([
        'waha' => 'WAHA',
        'evolution' => 'Evolution API',
    ]);
});

it('normalizes evolution aliases to evolution key', function () {
    $service = new TenantGlobalProviderCatalogService();

    expect($service->normalizeProvider('evolution-api'))->toBe('evolution');
    expect($service->normalizeProvider('evolution_api'))->toBe('evolution');
    expect($service->normalizeProvider('whatsapp-evolution'))->toBe('evolution');
    expect($service->normalizeProvider('EVO_API'))->toBe('evolution');
});

it('sanitizes providers keeping only supported and deduplicated keys', function () {
    $service = new TenantGlobalProviderCatalogService();

    $sanitized = $service->sanitizeProviders([
        'WAHA',
        'waha_gateway',
        'evolution-api',
        'EVOLUTION',
        'meta',
        '',
    ]);

    expect($sanitized)->toBe(['waha', 'evolution']);
});

it('resolves tenant global provider from enabled list using normalized key', function () {
    $service = new class extends TenantGlobalProviderCatalogService {
        public function enabledProviders(): array
        {
            return ['waha', 'evolution'];
        }
    };

    expect($service->resolveTenantGlobalProvider('whatsapp-evolution'))->toBe('evolution');
    expect($service->resolveTenantGlobalProvider(''))->toBe('waha');
});

it('does not silently fallback when tenant selected provider is disabled', function () {
    $service = new class extends TenantGlobalProviderCatalogService {
        public function enabledProviders(): array
        {
            return ['waha'];
        }
    };

    expect($service->resolveTenantGlobalProvider('evolution'))->toBeNull();
});
