<?php

use App\Models\Platform\SystemNotification;
use App\Models\Tenant\Notification as TenantNotification;
use Tests\TestCase;

uses(TestCase::class);

function readNotificationFile(string $relativePath): string
{
    $content = file_get_contents(base_path($relativePath));
    expect($content)->not->toBeFalse();

    return (string) $content;
}

function assertNoMojibake(string $content): void
{
    $forbidden = [
        "Notifica\u{00C3}\u{00A7}",
        "Configura\u{00C3}\u{00A7}",
        "h\u{00C3}\u{00A1}",
        "formul\u{00C3}",
        "\u{00C3}",
        "\u{00C2}",
        "\u{FFFD}",
    ];

    foreach ($forbidden as $token) {
        expect($content)->not->toContain($token);
    }
}

it('guards tenant notification views against raw textual regressions', function () {
    $tenantFiles = [
        'resources/views/layouts/tailadmin/notifications.blade.php' => [
            '$notification->meta_label',
        ],
        'resources/views/layouts/connect_plus/notifications.blade.php' => [
            '$notification->meta_label',
        ],
        'resources/views/tenant/notifications/index.blade.php' => [
            '$notification->status_label',
            '$notification->meta_label',
        ],
        'resources/views/tenant/notifications/show.blade.php' => [
            '$notification->type_label',
            '$notification->meta_label',
        ],
        'app/Http/Controllers/Tenant/Reports/NotificationReportController.php' => [
            '$notification->type_label',
        ],
        'resources/views/tenant/reports/notifications/pdf.blade.php' => [
            '$row->type_label',
        ],
    ];

    foreach ($tenantFiles as $file => $mustContain) {
        $content = readNotificationFile($file);

        foreach ($mustContain as $needle) {
            expect($content)->toContain($needle);
        }

        expect($content)->not->toContain('diffForHumans(');
        expect((int) preg_match('/\{\{\s*\$notification->type\s*(\?\?|}})/', $content))->toBe(0);
        expect((int) preg_match('/\{\{\s*\$notification->status\s*(\?\?|}})/', $content))->toBe(0);
        expect((int) preg_match('/\{\{\s*\$row->type\s*(\?\?|}})/', $content))->toBe(0);
        expect($content)->not->toContain('1 day ago');
        expect($content)->not->toContain('4 days ago');

        assertNoMojibake($content);
    }
});

it('guards platform system notification views against raw textual regressions', function () {
    $platformFiles = [
        'resources/views/layouts/freedash/notification.blade.php' => [
            '$n->created_at_human',
        ],
        'resources/views/platform/system_notifications/index.blade.php' => [
            '$notification->context_label',
            '$notification->level_label',
            '$notification->status_label',
        ],
        'resources/views/platform/system_notifications/show.blade.php' => [
            '$notification->context_label',
            '$notification->level_label',
        ],
    ];

    foreach ($platformFiles as $file => $mustContain) {
        $content = readNotificationFile($file);

        foreach ($mustContain as $needle) {
            expect($content)->toContain($needle);
        }

        expect($content)->not->toContain('diffForHumans(');
        expect($content)->not->toContain('1 day ago');
        expect($content)->not->toContain('4 days ago');

        assertNoMojibake($content);
    }
});

it('keeps tenant notification labels and relative time in pt-BR', function () {
    $first = new TenantNotification();
    $first->setRawAttributes([
        'type' => 'appointment',
        'status' => 'new',
        'created_at' => now()->subDay()->toDateTimeString(),
    ], true);

    $second = new TenantNotification();
    $second->setRawAttributes([
        'type' => 'form_response',
        'status' => 'read',
        'created_at' => now()->subDays(4)->toDateTimeString(),
    ], true);

    expect($first->type_label)->toBe('Agendamento')
        ->and($first->status_label)->toBe('Nova')
        ->and($first->created_at_human)->toBe('há 1 dia');

    expect($second->type_label)->toBe('Resposta de formulário')
        ->and($second->status_label)->toBe('Lida')
        ->and($second->created_at_human)->toBe('há 4 dias');

    $serialized = $second->toArray();
    expect($serialized)->toHaveKeys(['type_label', 'status_label', 'created_at_human', 'meta_label']);
    expect((string) ($serialized['meta_label'] ?? ''))->toContain('Resposta de formulário');
});

it('keeps platform system notification labels and relative time in pt-BR', function () {
    $notification = new SystemNotification();
    $notification->setRawAttributes([
        'status' => 'read',
        'level' => 'warning',
        'context' => 'payment',
        'created_at' => now()->subDays(4)->toDateTimeString(),
    ], true);

    expect($notification->status_label)->toBe('Lida')
        ->and($notification->level_label)->toBe('Aviso')
        ->and($notification->context_label)->toBe('Pagamento')
        ->and($notification->created_at_human)->toBe('há 4 dias');

    $serialized = $notification->toArray();
    expect($serialized)->toHaveKeys(['status_label', 'level_label', 'context_label', 'created_at_human']);
});
