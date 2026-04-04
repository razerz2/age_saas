<?php

use App\Http\Requests\Tenant\Integrations\StoreIntegrationRequest;
use App\Http\Requests\Tenant\Integrations\UpdateIntegrationRequest;
use Tests\TestCase;

uses(TestCase::class);

it('normalizes valid json config for store integration request', function () {
    $normalized = StoreIntegrationRequest::normalizeConfig('{"api_key":"abc","enabled":true}');

    expect($normalized)
        ->toBeArray()
        ->and($normalized['api_key'] ?? null)->toBe('abc')
        ->and($normalized['enabled'] ?? null)->toBeTrue();
});

it('returns null for invalid json config on store integration request', function () {
    expect(StoreIntegrationRequest::normalizeConfig('{invalid json'))
        ->toBeNull();

    expect(StoreIntegrationRequest::normalizeConfig('"string"'))
        ->toBeNull();
});

it('normalizes valid json config for update integration request', function () {
    $normalized = UpdateIntegrationRequest::normalizeConfig('[{"k":"v"}]');

    expect($normalized)
        ->toBeArray()
        ->and($normalized[0]['k'] ?? null)->toBe('v');
});

it('returns null for invalid json config on update integration request', function () {
    expect(UpdateIntegrationRequest::normalizeConfig('null'))
        ->toBeNull();

    expect(UpdateIntegrationRequest::normalizeConfig(''))
        ->toBeNull();
});
