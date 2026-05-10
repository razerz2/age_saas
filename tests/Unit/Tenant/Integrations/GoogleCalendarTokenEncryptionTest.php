<?php

use App\Models\Tenant\GoogleCalendarToken;
use Tests\TestCase;

uses(TestCase::class);

it('reads legacy google calendar token values without encryption', function () {
    $legacyAccessToken = [
        'access_token' => 'legacy-access-token',
        'token_type' => 'Bearer',
        'expires_in' => 3600,
    ];

    $model = new GoogleCalendarToken();
    $model->setRawAttributes([
        'access_token' => json_encode($legacyAccessToken, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'refresh_token' => 'legacy-refresh-token',
    ], true);

    expect($model->access_token)->toBe($legacyAccessToken)
        ->and($model->refresh_token)->toBe('legacy-refresh-token');
});

it('stores new google calendar token values encrypted', function () {
    $newAccessToken = [
        'access_token' => 'new-access-token',
        'token_type' => 'Bearer',
        'expires_in' => 3599,
    ];

    $model = new GoogleCalendarToken();
    $model->access_token = $newAccessToken;
    $model->refresh_token = 'new-refresh-token';

    $rawAttributes = $model->getAttributes();
    $rawAccessToken = (string) ($rawAttributes['access_token'] ?? '');
    $rawRefreshToken = (string) ($rawAttributes['refresh_token'] ?? '');

    $decodedAccessToken = json_decode($rawAccessToken, true);

    expect(is_array($decodedAccessToken))->toBeTrue()
        ->and($decodedAccessToken[GoogleCalendarToken::ACCESS_TOKEN_ENCRYPTED_FLAG_KEY] ?? null)->toBeTrue()
        ->and(is_string($decodedAccessToken[GoogleCalendarToken::ACCESS_TOKEN_ENCRYPTED_PAYLOAD_KEY] ?? null))->toBeTrue()
        ->and(str_contains($rawAccessToken, 'new-access-token'))->toBeFalse()
        ->and($rawRefreshToken)->not->toBe('new-refresh-token')
        ->and(GoogleCalendarToken::looksLikeEncryptedString($rawRefreshToken))->toBeTrue()
        ->and($model->access_token)->toBe($newAccessToken)
        ->and($model->refresh_token)->toBe('new-refresh-token');
});

it('detects encrypted access token payload marker', function () {
    $model = new GoogleCalendarToken();
    $model->access_token = ['access_token' => 'token'];

    $rawAccessToken = (string) ($model->getAttributes()['access_token'] ?? '');
    $decodedAccessToken = json_decode($rawAccessToken, true);

    expect(GoogleCalendarToken::isEncryptedAccessTokenPayload($decodedAccessToken))->toBeTrue();
});
