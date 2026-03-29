<?php

use App\Services\WhatsApp\WahaClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

it('normalizes binary qr payload to base64 without exposing raw binary in json body', function () {
    $binaryQr = hex2bin('89504e470d0a1a0a0000000d49484452') ?: '';

    Http::fake([
        'https://waha.test/api/clinica-teste/auth/qr?format=image' => Http::response(
            $binaryQr,
            200,
            ['Content-Type' => 'image/png']
        ),
    ]);

    $client = new WahaClient('https://waha.test', 'token', 'clinica-teste');
    $result = $client->getSessionQrCode('image');

    expect($result['ok'] ?? false)->toBeTrue();
    expect($result['body']['mimetype'] ?? null)->toBe('image/png');
    expect($result['body']['data'] ?? null)->toBe(base64_encode($binaryQr));
    expect($result['body']['is_data_url'] ?? null)->toBeFalse();
    expect(array_key_exists('raw', $result['body'] ?? []))->toBeFalse();
});

it('supports nested qr payload already returned as data url', function () {
    $qrDataUrl = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAAB';

    Http::fake([
        'https://waha.test/api/clinica-teste/auth/qr?format=image' => Http::response([
            'data' => [
                'qr' => $qrDataUrl,
            ],
            'mimetype' => 'image/png',
        ], 200),
    ]);

    $client = new WahaClient('https://waha.test', 'token', 'clinica-teste');
    $result = $client->getSessionQrCode('image');

    expect($result['ok'] ?? false)->toBeTrue();
    expect($result['body']['data'] ?? null)->toBe($qrDataUrl);
    expect($result['body']['is_data_url'] ?? null)->toBeTrue();
    expect($result['body']['mimetype'] ?? null)->toBe('image/png');
});

it('returns friendly normalized message when qr is not available yet', function () {
    Http::fake([
        'https://waha.test/api/clinica-teste/auth/qr?format=image' => Http::response([
            'error' => 'Session status is not as expected. Try again later.',
            'status' => 'FAILED',
            'expected' => ['SCAN_QR_CODE'],
        ], 422),
    ]);

    $client = new WahaClient('https://waha.test', 'token', 'clinica-teste');
    $result = $client->getSessionQrCode('image');

    expect($result['ok'] ?? true)->toBeFalse();
    expect($result['status'] ?? null)->toBe(422);
    expect(array_key_exists('data', $result['body'] ?? []))->toBeTrue();
    expect($result['body']['data'])->toBeNull();
    expect($result['body']['message'] ?? null)->toContain('Session status is not as expected');
});
