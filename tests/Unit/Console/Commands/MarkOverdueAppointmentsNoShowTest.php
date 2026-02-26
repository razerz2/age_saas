<?php

use App\Console\Commands\MarkOverdueAppointmentsNoShow;

test('normaliza grace e aplica default quando inválido', function () {
    expect(MarkOverdueAppointmentsNoShow::normalizeGraceMinutes(30))->toBe(30)
        ->and(MarkOverdueAppointmentsNoShow::normalizeGraceMinutes('5'))->toBe(5)
        ->and(MarkOverdueAppointmentsNoShow::normalizeGraceMinutes(-10))->toBe(0)
        ->and(MarkOverdueAppointmentsNoShow::normalizeGraceMinutes('abc'))->toBe(15);
});

test('normaliza timezone válida e usa fallback quando inválida', function () {
    expect(MarkOverdueAppointmentsNoShow::normalizeTimezone('America/Manaus', 'America/Sao_Paulo'))->toBe('America/Manaus')
        ->and(MarkOverdueAppointmentsNoShow::normalizeTimezone('timezone-invalida', 'America/Sao_Paulo'))->toBe('America/Sao_Paulo')
        ->and(MarkOverdueAppointmentsNoShow::normalizeTimezone('', 'UTC'))->toBe('UTC');
});
