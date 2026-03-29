<?php

use App\Services\Providers\ProviderConfigResolver;
use App\Services\WhatsApp\TenantEvolutionGlobalInstanceService;
use App\Services\WhatsApp\TenantGlobalProviderCatalogService;
use Tests\TestCase;

uses(TestCase::class);

afterEach(function () {
    \Mockery::close();
});

it('uses tenant runtime evolution instance when global provider is evolution', function () {
    config([
        'services.whatsapp.evolution.base_url' => 'https://evolution.test',
        'services.whatsapp.evolution.api_key' => 'token-evolution',
        'services.whatsapp.evolution.instance' => 'global-fixed',
    ]);

    $catalog = \Mockery::mock(TenantGlobalProviderCatalogService::class);
    $catalog->shouldReceive('resolveTenantGlobalProvider')
        ->once()
        ->andReturn('evolution');
    app()->instance(TenantGlobalProviderCatalogService::class, $catalog);

    $instanceService = \Mockery::mock(TenantEvolutionGlobalInstanceService::class);
    $instanceService->shouldReceive('resolveRuntimeInstance')
        ->once()
        ->andReturn('clinica-teste');
    app()->instance(TenantEvolutionGlobalInstanceService::class, $instanceService);

    $resolver = new ProviderConfigResolver();
    $resolved = $resolver->resolveEvolutionConfig([
        'driver' => 'global',
        'global_provider' => 'evolution',
    ]);

    expect($resolved['instance'])->toBe('clinica-teste');
});

it('keeps global fixed evolution instance when selected global provider is not evolution', function () {
    config([
        'services.whatsapp.evolution.base_url' => 'https://evolution.test',
        'services.whatsapp.evolution.api_key' => 'token-evolution',
        'services.whatsapp.evolution.instance' => 'global-fixed',
    ]);

    $catalog = \Mockery::mock(TenantGlobalProviderCatalogService::class);
    $catalog->shouldReceive('resolveTenantGlobalProvider')
        ->once()
        ->andReturn('waha');
    app()->instance(TenantGlobalProviderCatalogService::class, $catalog);

    $instanceService = \Mockery::mock(TenantEvolutionGlobalInstanceService::class);
    $instanceService->shouldNotReceive('resolveRuntimeInstance');
    app()->instance(TenantEvolutionGlobalInstanceService::class, $instanceService);

    $resolver = new ProviderConfigResolver();
    $resolved = $resolver->resolveEvolutionConfig([
        'driver' => 'global',
        'global_provider' => 'waha',
    ]);

    expect($resolved['instance'])->toBe('global-fixed');
});
