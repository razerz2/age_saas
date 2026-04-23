<?php

use App\Models\Tenant\Campaign;
use App\Services\Tenant\CampaignRenderer;
use App\Services\Tenant\CampaignTemplateProviderResolver;
use App\Services\Tenant\TemplateRenderer;
use Tests\TestCase;

uses(TestCase::class);

it('tracks unknown placeholders when whatsapp context is incomplete', function () {
    $resolver = \Mockery::mock(CampaignTemplateProviderResolver::class);
    $resolver->shouldReceive('resolveWhatsAppProvider')->andReturn('waha');

    $renderer = new CampaignRenderer(new TemplateRenderer(), $resolver);
    $campaign = new Campaign([
        'content_json' => [
            'whatsapp' => [
                'provider' => 'waha',
                'composition_mode' => 'manual',
                'message_type' => 'text',
                'text' => 'Ola {{patient.name}}. Link {{links.public_booking}}',
            ],
        ],
    ]);

    $payload = $renderer->renderChannel($campaign, 'whatsapp', []);

    expect($payload['unknown_placeholders'] ?? [])
        ->toContain('patient.name')
        ->toContain('links.public_booking');
});

it('does not report unknown placeholders when whatsapp context is complete', function () {
    $resolver = \Mockery::mock(CampaignTemplateProviderResolver::class);
    $resolver->shouldReceive('resolveWhatsAppProvider')->andReturn('waha');

    $renderer = new CampaignRenderer(new TemplateRenderer(), $resolver);
    $campaign = new Campaign([
        'content_json' => [
            'whatsapp' => [
                'provider' => 'waha',
                'composition_mode' => 'manual',
                'message_type' => 'text',
                'text' => 'Ola {{patient.name}}. Link {{links.public_booking}}',
            ],
        ],
    ]);

    $payload = $renderer->renderChannel($campaign, 'whatsapp', [
        'patient' => [
            'name' => 'Rafael',
        ],
        'links' => [
            'public_booking' => 'https://example.test/public-booking',
        ],
    ]);

    expect($payload['unknown_placeholders'] ?? [])->toBe([])
        ->and($payload['text'] ?? null)->toBe('Ola Rafael. Link https://example.test/public-booking');
});

it('reports unknown placeholders when placeholder exists in context but has null value', function () {
    $resolver = \Mockery::mock(CampaignTemplateProviderResolver::class);
    $resolver->shouldReceive('resolveWhatsAppProvider')->andReturn('waha');

    $renderer = new CampaignRenderer(new TemplateRenderer(), $resolver);
    $campaign = new Campaign([
        'content_json' => [
            'whatsapp' => [
                'provider' => 'waha',
                'composition_mode' => 'manual',
                'message_type' => 'text',
                'text' => 'Portal {{links.portal}}',
            ],
        ],
    ]);

    $payload = $renderer->renderChannel($campaign, 'whatsapp', [
        'links' => [
            'portal' => null,
        ],
    ]);

    expect($payload['unknown_placeholders'] ?? [])->toContain('links.portal')
        ->and($payload['text'] ?? null)->toBe('Portal');
});
