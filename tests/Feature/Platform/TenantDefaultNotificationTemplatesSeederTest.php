<?php

use App\Models\Platform\TenantDefaultNotificationTemplate;
use Database\Seeders\TenantDefaultNotificationTemplatesSeeder;

it('seeds tenant default notification templates baseline', function () {
    app(TenantDefaultNotificationTemplatesSeeder::class)->run();

    $expectedKeys = [
        'appointment.pending_confirmation',
        'appointment.confirmed',
        'appointment.canceled',
        'appointment.expired',
        'waitlist.joined',
        'waitlist.offered',
    ];

    $rows = TenantDefaultNotificationTemplate::query()
        ->where('channel', 'whatsapp')
        ->whereIn('key', $expectedKeys)
        ->get();

    expect($rows)->toHaveCount(count($expectedKeys));

    $keys = $rows->pluck('key')->sort()->values()->all();
    $expected = $expectedKeys;
    sort($expected);

    expect($keys)->toBe($expected);
});

it('is idempotent when running tenant defaults seeder multiple times', function () {
    app(TenantDefaultNotificationTemplatesSeeder::class)->run();
    app(TenantDefaultNotificationTemplatesSeeder::class)->run();

    $rows = TenantDefaultNotificationTemplate::query()
        ->where('channel', 'whatsapp')
        ->get();

    expect($rows)->toHaveCount(6);

    $duplicates = TenantDefaultNotificationTemplate::query()
        ->selectRaw('channel, key, count(*) as total')
        ->groupBy('channel', 'key')
        ->havingRaw('count(*) > 1')
        ->get();

    expect($duplicates)->toHaveCount(0);
});

