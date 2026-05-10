<?php

use App\Console\Commands\EncryptGoogleCalendarTokensCommand;
use App\Models\Tenant\GoogleCalendarToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    config([
        'database.connections.tenant' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ],
    ]);

    DB::purge('tenant');
    DB::reconnect('tenant');

    Schema::connection('tenant')->dropIfExists('google_calendar_tokens');
    Schema::connection('tenant')->create('google_calendar_tokens', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->string('doctor_id');
        $table->text('access_token');
        $table->text('refresh_token')->nullable();
        $table->timestamp('expires_at')->nullable();
        $table->timestamps();
    });
});

function makeEncryptGoogleTokensCommand(): EncryptGoogleCalendarTokensCommand
{
    return new class extends EncryptGoogleCalendarTokensCommand
    {
        public function runProcessCurrentTenant(bool $dryRun): array
        {
            return $this->processCurrentTenant($dryRun);
        }
    };
}

it('dry-run does not alter legacy google calendar tokens', function () {
    $legacyAccessToken = [
        'access_token' => 'legacy-access',
        'token_type' => 'Bearer',
        'expires_in' => 3600,
    ];

    DB::connection('tenant')->table('google_calendar_tokens')->insert([
        'id' => 'token-legacy-dry-run',
        'doctor_id' => 'doctor-legacy-dry-run',
        'access_token' => json_encode($legacyAccessToken, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'refresh_token' => 'legacy-refresh-dry-run',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $command = makeEncryptGoogleTokensCommand();
    $result = $command->runProcessCurrentTenant(true);

    $row = DB::connection('tenant')
        ->table('google_calendar_tokens')
        ->where('id', 'token-legacy-dry-run')
        ->first();

    expect($result['found'])->toBe(1)
        ->and($result['migrated'])->toBe(1)
        ->and($result['already_encrypted'])->toBe(0)
        ->and($result['errors'])->toBe(0)
        ->and(json_decode((string) $row->access_token, true))->toBe($legacyAccessToken)
        ->and((string) $row->refresh_token)->toBe('legacy-refresh-dry-run');
});

it('real run migrates legacy google calendar tokens to encrypted format', function () {
    $legacyAccessToken = [
        'access_token' => 'legacy-access-real',
        'token_type' => 'Bearer',
        'expires_in' => 3599,
    ];

    DB::connection('tenant')->table('google_calendar_tokens')->insert([
        'id' => 'token-legacy-real',
        'doctor_id' => 'doctor-legacy-real',
        'access_token' => json_encode($legacyAccessToken, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'refresh_token' => 'legacy-refresh-real',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $command = makeEncryptGoogleTokensCommand();
    $result = $command->runProcessCurrentTenant(false);

    $row = DB::connection('tenant')
        ->table('google_calendar_tokens')
        ->where('id', 'token-legacy-real')
        ->first();

    $rawAccessToken = json_decode((string) $row->access_token, true);

    expect($result['found'])->toBe(1)
        ->and($result['migrated'])->toBe(1)
        ->and($result['already_encrypted'])->toBe(0)
        ->and($result['errors'])->toBe(0)
        ->and(GoogleCalendarToken::isEncryptedAccessTokenPayload($rawAccessToken))->toBeTrue()
        ->and((string) $row->refresh_token)->not->toBe('legacy-refresh-real')
        ->and(GoogleCalendarToken::looksLikeEncryptedString((string) $row->refresh_token))->toBeTrue();

    $token = GoogleCalendarToken::query()->find('token-legacy-real');

    expect($token)->not->toBeNull()
        ->and($token->access_token)->toBe($legacyAccessToken)
        ->and($token->refresh_token)->toBe('legacy-refresh-real');
});

