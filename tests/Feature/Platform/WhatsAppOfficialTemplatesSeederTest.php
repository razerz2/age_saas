<?php

use App\Models\Platform\WhatsAppOfficialTemplate;
use Database\Seeders\WhatsAppOfficialTemplatesSeeder;

it('seeds the platform saas baseline for official whatsapp templates', function () {
    app(WhatsAppOfficialTemplatesSeeder::class)->run();

    $expectedKeys = [
        'invoice.created',
        'invoice.upcoming_due',
        'invoice.overdue',
        'tenant.suspended_due_to_overdue',
        'security.2fa_code',
        'tenant.welcome',
        'subscription.created',
        'trial.ends_in_7_days',
        'trial.ends_in_3_days',
        'trial.ends_today',
        'trial.expired',
        'subscription.recovery_started',
        'credentials.resent',
    ];

    $rows = WhatsAppOfficialTemplate::query()
        ->officialProvider()
        ->where('version', 1)
        ->whereIn('key', $expectedKeys)
        ->get();

    expect($rows)->toHaveCount(count($expectedKeys));

    $keys = $rows->pluck('key')->sort()->values()->all();
    $sortedExpected = $expectedKeys;
    sort($sortedExpected);

    expect($keys)->toBe($sortedExpected);

    $security = $rows->firstWhere('key', 'security.2fa_code');
    expect($security)->not->toBeNull()
        ->and($security?->category)->toBe('SECURITY');

    $nonSecurityCount = $rows
        ->filter(fn (WhatsAppOfficialTemplate $template): bool => $template->key !== 'security.2fa_code')
        ->where('category', 'UTILITY')
        ->count();

    expect($nonSecurityCount)->toBe(count($expectedKeys) - 1);
});

it('is idempotent when running the official whatsapp templates seeder multiple times', function () {
    app(WhatsAppOfficialTemplatesSeeder::class)->run();
    app(WhatsAppOfficialTemplatesSeeder::class)->run();

    $rows = WhatsAppOfficialTemplate::query()
        ->officialProvider()
        ->where('version', 1)
        ->get();

    expect($rows)->toHaveCount(13);

    $duplicates = WhatsAppOfficialTemplate::query()
        ->officialProvider()
        ->selectRaw('key, version, count(*) as total')
        ->groupBy('key', 'version')
        ->havingRaw('count(*) > 1')
        ->get();

    expect($duplicates)->toHaveCount(0);
});
