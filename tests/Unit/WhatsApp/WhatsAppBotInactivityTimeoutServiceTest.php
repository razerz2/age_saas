<?php

use App\Models\Tenant\WhatsAppBotSession;
use App\Services\Tenant\WhatsAppBot\DTO\InboundMessage;
use App\Services\Tenant\WhatsAppBot\DTO\OutboundMessage;
use App\Services\Tenant\WhatsAppBot\Provider\Contracts\WhatsAppBotProviderAdapterInterface;
use App\Services\Tenant\WhatsAppBot\Provider\WhatsAppBotProviderAdapterFactory;
use App\Services\Tenant\WhatsAppBot\Provider\WhatsAppBotProviderResolver;
use App\Services\Tenant\WhatsAppBot\WhatsAppBotInactivityTimeoutService;
use App\Services\Tenant\WhatsAppBotConfigService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class);

final class FakeInactivityAdapter implements WhatsAppBotProviderAdapterInterface
{
    public int $sentCount = 0;
    public ?OutboundMessage $lastMessage = null;
    /** @var array<int, string> */
    public array $sentTo = [];

    /**
     * @param array<int, string> $failPhones
     */
    public function __construct(private readonly array $failPhones = [])
    {
    }

    public function providerKey(): string
    {
        return 'waha';
    }

    public function normalizeInbound(array $payload): ?InboundMessage
    {
        return null;
    }

    public function sendOutbound(OutboundMessage $message): bool
    {
        $this->sentCount++;
        $this->lastMessage = $message;
        $this->sentTo[] = $message->to;

        if (in_array($message->to, $this->failPhones, true)) {
            return false;
        }

        return true;
    }
}

function inactivityService(): WhatsAppBotInactivityTimeoutService
{
    return new WhatsAppBotInactivityTimeoutService(
        configService: Mockery::mock(WhatsAppBotConfigService::class),
        providerResolver: Mockery::mock(WhatsAppBotProviderResolver::class),
        adapterFactory: Mockery::mock(WhatsAppBotProviderAdapterFactory::class),
    );
}

/**
 * @param array<string, mixed> $overrides
 */
function inactivitySettings(array $overrides = []): array
{
    return array_replace_recursive([
        'enabled' => true,
        'session' => [
            'idle_timeout_minutes' => 30,
            'end_on_inactivity' => true,
            'clear_context_on_end' => true,
            'allow_resume_previous' => false,
        ],
        'messages' => [
            'inactivity_exit' => 'Sessão encerrada por inatividade (automático).',
        ],
    ], $overrides);
}

/**
 * @param array<string, mixed> $state
 * @param array<string, mixed> $meta
 */
function inactivitySession(
    string $status = 'active',
    ?Carbon $lastInboundAt = null,
    array $state = [],
    array $meta = []
): WhatsAppBotSession {
    $session = new WhatsAppBotSession();
    $session->id = 'session-1';
    $session->status = $status;
    $session->channel = 'whatsapp';
    $session->provider = 'waha';
    $session->contact_phone = '5567999999999';
    $session->current_flow = 'schedule';
    $session->current_step = 'schedule.awaiting_date';
    $session->state = $state;
    $session->meta = $meta;
    $session->created_at = Carbon::parse('2026-03-29 09:00:00');
    $session->updated_at = Carbon::parse('2026-03-29 09:20:00');
    $session->last_inbound_message_at = $lastInboundAt;

    return $session;
}

/**
 * @param array<string, mixed> $settings
 */
function inactivitySweepService(array $settings, ?FakeInactivityAdapter $adapter = null): WhatsAppBotInactivityTimeoutService
{
    $configService = Mockery::mock(WhatsAppBotConfigService::class);
    $configService->shouldReceive('getSettings')->andReturn($settings);

    $providerResolver = Mockery::mock(WhatsAppBotProviderResolver::class);
    $adapterFactory = Mockery::mock(WhatsAppBotProviderAdapterFactory::class);

    if ($adapter instanceof FakeInactivityAdapter) {
        $providerResolver->shouldReceive('resolveForCurrentTenant')
            ->andReturn([
                'enabled' => true,
                'provider' => 'waha',
                'effective_config' => [],
            ]);
        $providerResolver->shouldReceive('applyRuntimeConfig')->andReturnNull();
        $adapterFactory->shouldReceive('isSupported')->with('waha')->andReturnTrue();
        $adapterFactory->shouldReceive('make')->with('waha')->andReturn($adapter);
    } else {
        $providerResolver->shouldReceive('resolveForCurrentTenant')
            ->andReturn([
                'enabled' => false,
                'provider' => 'waha',
                'effective_config' => [],
            ]);
        $adapterFactory->shouldReceive('isSupported')->never();
        $adapterFactory->shouldReceive('make')->never();
    }

    return new WhatsAppBotInactivityTimeoutService(
        configService: $configService,
        providerResolver: $providerResolver,
        adapterFactory: $adapterFactory,
    );
}

/**
 * @param array<string, mixed> $attributes
 */
function persistInactivitySession(array $attributes = []): WhatsAppBotSession
{
    $session = new WhatsAppBotSession();
    $session->id = (string) ($attributes['id'] ?? fake()->uuid());
    $session->tenant_id = (string) ($attributes['tenant_id'] ?? 'tenant-test');
    $session->channel = (string) ($attributes['channel'] ?? 'whatsapp');
    $session->provider = (string) ($attributes['provider'] ?? 'waha');
    $session->contact_phone = (string) ($attributes['contact_phone'] ?? '5567999999999');
    $session->contact_identifier = $attributes['contact_identifier'] ?? null;
    $session->status = (string) ($attributes['status'] ?? 'active');
    $session->current_flow = (string) ($attributes['current_flow'] ?? 'menu');
    $session->current_step = (string) ($attributes['current_step'] ?? 'menu.awaiting_option');
    $session->state = (array) ($attributes['state'] ?? []);
    $session->meta = (array) ($attributes['meta'] ?? []);
    $session->last_inbound_message_type = (string) ($attributes['last_inbound_message_type'] ?? 'chat');
    $session->last_inbound_message_at = $attributes['last_inbound_message_at'] ?? null;
    $session->last_outbound_message_at = $attributes['last_outbound_message_at'] ?? null;
    $session->last_payload = (array) ($attributes['last_payload'] ?? []);
    $session->created_at = $attributes['created_at'] ?? Carbon::parse('2026-03-29 09:00:00');
    $session->updated_at = $attributes['updated_at'] ?? Carbon::parse('2026-03-29 09:10:00');
    $session->save();

    return $session;
}

beforeEach(function () {
    config()->set('database.connections.tenant', [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
        'foreign_key_constraints' => false,
    ]);

    DB::purge('tenant');
    DB::reconnect('tenant');

    Schema::connection('tenant')->dropIfExists('whatsapp_bot_sessions');
    Schema::connection('tenant')->create('whatsapp_bot_sessions', function (Blueprint $table): void {
        $table->uuid('id')->primary();
        $table->uuid('tenant_id');
        $table->string('channel', 32)->default('whatsapp');
        $table->string('provider', 32)->nullable();
        $table->string('contact_phone', 32);
        $table->string('contact_identifier')->nullable();
        $table->string('status', 32)->default('active');
        $table->string('current_flow', 100)->nullable();
        $table->string('current_step', 100)->nullable();
        $table->json('state')->nullable();
        $table->string('last_inbound_message_type', 32)->nullable();
        $table->dateTime('last_inbound_message_at')->nullable();
        $table->dateTime('last_outbound_message_at')->nullable();
        $table->json('last_payload')->nullable();
        $table->json('meta')->nullable();
        $table->timestamps();
    });
});

it('closes an expired active session and sends inactivity message once', function () {
    $service = inactivityService();
    $adapter = new FakeInactivityAdapter();

    $now = Carbon::parse('2026-03-29 10:00:00');
    $session = inactivitySession(
        status: 'active',
        lastInboundAt: Carbon::parse('2026-03-29 09:00:00'),
        state: [
            'patient_id' => 'patient-1',
            'schedule' => ['selected_doctor' => ['id' => 'doctor-1']],
            'pending_intent' => 'schedule',
        ]
    );

    $result = $service->processSession(
        session: $session,
        settings: inactivitySettings(),
        adapter: $adapter,
        now: $now,
        persist: false
    );

    expect($result['processed'])->toBeTrue()
        ->and($result['sent'])->toBeTrue()
        ->and($session->status)->toBe('ended')
        ->and($session->current_flow)->toBe('menu')
        ->and($session->current_step)->toBe('menu.awaiting_option')
        ->and($adapter->sentCount)->toBe(1)
        ->and(data_get($session->meta, 'last_end_reason'))->toBe('inactivity_timeout')
        ->and(data_get($session->state, 'patient_id'))->toBeNull()
        ->and(data_get($session->state, 'schedule'))->toBe([]);
});

it('does not send inactivity message twice for the same session after being closed', function () {
    $service = inactivityService();
    $adapter = new FakeInactivityAdapter();

    $now = Carbon::parse('2026-03-29 10:00:00');
    $session = inactivitySession(
        status: 'active',
        lastInboundAt: Carbon::parse('2026-03-29 09:00:00')
    );

    $first = $service->processSession(
        session: $session,
        settings: inactivitySettings(),
        adapter: $adapter,
        now: $now,
        persist: false
    );

    $second = $service->processSession(
        session: $session,
        settings: inactivitySettings(),
        adapter: $adapter,
        now: $now->copy()->addMinute(),
        persist: false
    );

    expect($first['processed'])->toBeTrue()
        ->and($second['processed'])->toBeFalse()
        ->and($second['reason'])->toBe('session_not_active')
        ->and($adapter->sentCount)->toBe(1);
});

it('does not resend inactivity message for an already ended session', function () {
    $service = inactivityService();
    $adapter = new FakeInactivityAdapter();

    $session = inactivitySession(
        status: 'ended',
        lastInboundAt: Carbon::parse('2026-03-29 09:00:00')
    );

    $result = $service->processSession(
        session: $session,
        settings: inactivitySettings(),
        adapter: $adapter,
        now: Carbon::parse('2026-03-29 10:00:00'),
        persist: false
    );

    expect($result['processed'])->toBeFalse()
        ->and($result['reason'])->toBe('session_not_active')
        ->and($adapter->sentCount)->toBe(0);
});

it('does not close session when end_on_inactivity is disabled', function () {
    $service = inactivityService();
    $adapter = new FakeInactivityAdapter();

    $session = inactivitySession(
        status: 'active',
        lastInboundAt: Carbon::parse('2026-03-29 09:00:00')
    );

    $result = $service->processSession(
        session: $session,
        settings: inactivitySettings([
            'session' => [
                'end_on_inactivity' => false,
            ],
        ]),
        adapter: $adapter,
        now: Carbon::parse('2026-03-29 10:00:00'),
        persist: false
    );

    expect($result['processed'])->toBeFalse()
        ->and($result['reason'])->toBe('end_on_inactivity_disabled')
        ->and($session->status)->toBe('active')
        ->and($adapter->sentCount)->toBe(0);
});

it('keeps dry-run and real run consistent for the same expired candidate set', function () {
    $now = Carbon::parse('2026-03-29 11:00:00');
    persistInactivitySession([
        'id' => 'session-dry-real',
        'contact_phone' => '5567999990001',
        'status' => 'active',
        'last_inbound_message_at' => Carbon::parse('2026-03-29 09:00:00'),
    ]);

    $dryRunStats = inactivitySweepService(inactivitySettings(), new FakeInactivityAdapter())
        ->sweepCurrentTenant(true, $now);

    expect($dryRunStats['candidates'])->toBe(1)
        ->and($dryRunStats['processed'])->toBe(1)
        ->and($dryRunStats['sent'])->toBe(0)
        ->and($dryRunStats['failed_send'])->toBe(0);

    $afterDryRun = WhatsAppBotSession::query()->find('session-dry-real');
    expect($afterDryRun)->not->toBeNull()
        ->and($afterDryRun?->status)->toBe('active');

    $adapter = new FakeInactivityAdapter();
    $realStats = inactivitySweepService(inactivitySettings(), $adapter)
        ->sweepCurrentTenant(false, $now);

    expect($realStats['candidates'])->toBe(1)
        ->and($realStats['processed'])->toBe(1)
        ->and($realStats['sent'])->toBe(1)
        ->and($realStats['failed_send'])->toBe(0)
        ->and($adapter->sentCount)->toBe(1);

    $afterReal = WhatsAppBotSession::query()->find('session-dry-real');
    expect($afterReal)->not->toBeNull()
        ->and($afterReal?->status)->toBe('ended');
});

it('does not reprocess already ended session on immediate rerun', function () {
    $now = Carbon::parse('2026-03-29 11:00:00');
    persistInactivitySession([
        'id' => 'session-ended-rerun',
        'contact_phone' => '5567999990002',
        'status' => 'active',
        'last_inbound_message_at' => Carbon::parse('2026-03-29 09:00:00'),
    ]);

    inactivitySweepService(inactivitySettings(), new FakeInactivityAdapter())
        ->sweepCurrentTenant(false, $now);

    $stats = inactivitySweepService(inactivitySettings(), new FakeInactivityAdapter())
        ->sweepCurrentTenant(false, $now->copy()->addSeconds(10));

    expect($stats['candidates'])->toBe(0)
        ->and($stats['processed'])->toBe(0)
        ->and($stats['sent'])->toBe(0)
        ->and($stats['failed_send'])->toBe(0);
});

it('keeps session active and schedules retry when inactivity message fails to send', function () {
    $now = Carbon::parse('2026-03-29 11:00:00');
    $phone = '5567999990003';

    persistInactivitySession([
        'id' => 'session-send-fail',
        'contact_phone' => $phone,
        'status' => 'active',
        'last_inbound_message_at' => Carbon::parse('2026-03-29 09:00:00'),
    ]);

    $failingAdapter = new FakeInactivityAdapter([$phone]);
    $first = inactivitySweepService(inactivitySettings(), $failingAdapter)
        ->sweepCurrentTenant(false, $now);

    expect($first['candidates'])->toBe(1)
        ->and($first['processed'])->toBe(1)
        ->and($first['sent'])->toBe(0)
        ->and($first['failed_send'])->toBe(1)
        ->and($first['retry_scheduled'])->toBe(1);

    $session = WhatsAppBotSession::query()->find('session-send-fail');
    expect($session)->not->toBeNull()
        ->and($session?->status)->toBe('active')
        ->and(data_get($session?->meta, 'inactivity_timeout.status'))->toBe('send_failed')
        ->and((string) data_get($session?->meta, 'inactivity_timeout.next_retry_at', ''))->not->toBe('');

    $immediate = inactivitySweepService(inactivitySettings(), new FakeInactivityAdapter([$phone]))
        ->sweepCurrentTenant(false, $now->copy()->addSeconds(10));

    expect($immediate['candidates'])->toBe(0)
        ->and($immediate['processed'])->toBe(0);

    $afterBackoff = inactivitySweepService(inactivitySettings(), new FakeInactivityAdapter([$phone]))
        ->sweepCurrentTenant(false, $now->copy()->addMinutes(6));

    expect($afterBackoff['candidates'])->toBe(1)
        ->and($afterBackoff['failed_send'])->toBe(1);
});

it('deduplicates multiple expired active sessions for the same phone in a single sweep', function () {
    $now = Carbon::parse('2026-03-29 11:00:00');
    $phone = '5567999990004';

    persistInactivitySession([
        'id' => 'session-dup-1',
        'contact_phone' => $phone,
        'status' => 'active',
        'last_inbound_message_at' => Carbon::parse('2026-03-29 08:50:00'),
    ]);

    persistInactivitySession([
        'id' => 'session-dup-2',
        'contact_phone' => $phone,
        'status' => 'active',
        'last_inbound_message_at' => Carbon::parse('2026-03-29 08:55:00'),
    ]);

    $adapter = new FakeInactivityAdapter();
    $stats = inactivitySweepService(inactivitySettings(), $adapter)
        ->sweepCurrentTenant(false, $now);

    expect($stats['candidates'])->toBe(1)
        ->and($stats['sent'])->toBe(1)
        ->and($adapter->sentCount)->toBe(1);
});

it('considers an active expired session as candidate after new inbound activity post previous inactivity closure', function () {
    $now = Carbon::parse('2026-03-29 11:00:00');

    persistInactivitySession([
        'id' => 'session-reactivated',
        'contact_phone' => '5567999990010',
        'status' => 'active',
        'last_inbound_message_at' => Carbon::parse('2026-03-29 09:00:00'),
        'meta' => [
            'inactivity_timeout' => [
                'status' => 'sent',
                'sent_at' => '2026-03-29 08:00:00',
                'closed_at' => '2026-03-29 08:00:00',
            ],
        ],
    ]);

    $adapter = new FakeInactivityAdapter();
    $stats = inactivitySweepService(inactivitySettings(), $adapter)
        ->sweepCurrentTenant(false, $now);

    expect($stats['candidates'])->toBe(1)
        ->and($stats['processed'])->toBe(1)
        ->and($stats['sent'])->toBe(1)
        ->and($adapter->sentCount)->toBe(1);
});

it('does not select a recently active session as candidate', function () {
    $now = Carbon::parse('2026-03-29 11:00:00');
    persistInactivitySession([
        'id' => 'session-recent',
        'tenant_id' => 'tenant-test',
        'contact_phone' => '5567999990020',
        'status' => 'active',
        'last_inbound_message_at' => Carbon::parse('2026-03-29 10:40:30'),
    ]);

    $adapter = new FakeInactivityAdapter();
    $stats = inactivitySweepService(inactivitySettings(), $adapter)
        ->sweepCurrentTenant(false, $now, 'tenant-test');

    expect($stats['candidates'])->toBe(0)
        ->and($stats['processed'])->toBe(0)
        ->and($stats['sent'])->toBe(0)
        ->and($adapter->sentCount)->toBe(0);
});

it('selects session at exact idle-timeout boundary as candidate', function () {
    $now = Carbon::parse('2026-03-29 11:00:00');
    persistInactivitySession([
        'id' => 'session-boundary',
        'tenant_id' => 'tenant-test',
        'contact_phone' => '5567999990021',
        'status' => 'active',
        'last_inbound_message_at' => Carbon::parse('2026-03-29 10:30:00'),
    ]);

    $adapter = new FakeInactivityAdapter();
    $stats = inactivitySweepService(inactivitySettings(), $adapter)
        ->sweepCurrentTenant(false, $now, 'tenant-test');

    expect($stats['candidates'])->toBe(1)
        ->and($stats['processed'])->toBe(1)
        ->and($stats['sent'])->toBe(1)
        ->and($adapter->sentCount)->toBe(1);
});

it('does not include sessions from another tenant when tenant filter is applied', function () {
    $now = Carbon::parse('2026-03-29 11:00:00');
    persistInactivitySession([
        'id' => 'session-other-tenant',
        'tenant_id' => 'tenant-other',
        'contact_phone' => '5567999990030',
        'status' => 'active',
        'last_inbound_message_at' => Carbon::parse('2026-03-29 09:00:00'),
    ]);

    $adapter = new FakeInactivityAdapter();
    $stats = inactivitySweepService(inactivitySettings(), $adapter)
        ->sweepCurrentTenant(false, $now, 'tenant-test');

    expect($stats['candidates'])->toBe(0)
        ->and($stats['processed'])->toBe(0)
        ->and($stats['sent'])->toBe(0)
        ->and($adapter->sentCount)->toBe(0);
});

it('does not include sessions with non-active status', function () {
    $now = Carbon::parse('2026-03-29 11:00:00');
    persistInactivitySession([
        'id' => 'session-ended-status',
        'tenant_id' => 'tenant-test',
        'contact_phone' => '5567999990040',
        'status' => 'ended',
        'last_inbound_message_at' => Carbon::parse('2026-03-29 09:00:00'),
    ]);

    $adapter = new FakeInactivityAdapter();
    $stats = inactivitySweepService(inactivitySettings(), $adapter)
        ->sweepCurrentTenant(false, $now, 'tenant-test');

    expect($stats['candidates'])->toBe(0)
        ->and($stats['processed'])->toBe(0)
        ->and($stats['sent'])->toBe(0)
        ->and($adapter->sentCount)->toBe(0);
});

it('finds and closes only the correct eligible session in command-like sweep', function () {
    $now = Carbon::parse('2026-03-29 11:00:00');

    persistInactivitySession([
        'id' => 'session-eligible',
        'tenant_id' => 'tenant-test',
        'contact_phone' => '5567999990050',
        'status' => 'active',
        'last_inbound_message_at' => Carbon::parse('2026-03-29 09:00:00'),
    ]);

    persistInactivitySession([
        'id' => 'session-recent-2',
        'tenant_id' => 'tenant-test',
        'contact_phone' => '5567999990051',
        'status' => 'active',
        'last_inbound_message_at' => Carbon::parse('2026-03-29 10:50:00'),
    ]);

    persistInactivitySession([
        'id' => 'session-ended-2',
        'tenant_id' => 'tenant-test',
        'contact_phone' => '5567999990052',
        'status' => 'ended',
        'last_inbound_message_at' => Carbon::parse('2026-03-29 09:00:00'),
    ]);

    persistInactivitySession([
        'id' => 'session-other-tenant-2',
        'tenant_id' => 'tenant-other',
        'contact_phone' => '5567999990053',
        'status' => 'active',
        'last_inbound_message_at' => Carbon::parse('2026-03-29 09:00:00'),
    ]);

    $adapter = new FakeInactivityAdapter();
    $stats = inactivitySweepService(inactivitySettings(), $adapter)
        ->sweepCurrentTenant(false, $now, 'tenant-test');

    expect($stats['candidates'])->toBe(1)
        ->and($stats['processed'])->toBe(1)
        ->and($stats['sent'])->toBe(1)
        ->and($stats['failed_send'])->toBe(0)
        ->and($adapter->sentCount)->toBe(1);

    $eligible = WhatsAppBotSession::query()->find('session-eligible');
    $recent = WhatsAppBotSession::query()->find('session-recent-2');
    $ended = WhatsAppBotSession::query()->find('session-ended-2');
    $otherTenant = WhatsAppBotSession::query()->find('session-other-tenant-2');

    expect($eligible?->status)->toBe('ended')
        ->and($recent?->status)->toBe('active')
        ->and($ended?->status)->toBe('ended')
        ->and($otherTenant?->status)->toBe('active');
});
