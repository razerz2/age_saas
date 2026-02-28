@php
    $automation = is_array($automation ?? null) ? $automation : [];

    $scheduleModeRaw = strtolower(trim((string) ($campaign->schedule_mode ?? '')));
    $scheduleMode = in_array($scheduleModeRaw, ['period', 'indefinite'], true)
        ? $scheduleModeRaw
        : null;

    $modeLabel = match ($scheduleMode) {
        'period' => 'Período',
        'indefinite' => 'Indefinida',
        default => 'Não definida',
    };

    $timezone = trim((string) ($campaign->timezone ?? ''));
    if ($timezone === '') {
        $timezone = trim((string) data_get($automation, 'timezone', ''));
    }
    if ($timezone === '') {
        $timezone = trim((string) ($automationTimezone ?? ''));
    }

    $startsAt = $campaign->starts_at ?? null;
    $endsAt = $campaign->ends_at ?? null;

    $weekdays = is_array($campaign->schedule_weekdays ?? null) ? $campaign->schedule_weekdays : [];
    $weekdays = collect($weekdays)
        ->filter(fn ($day) => is_numeric($day))
        ->map(fn ($day) => (int) $day)
        ->filter(fn ($day) => $day >= 0 && $day <= 6)
        ->unique()
        ->sort()
        ->values()
        ->all();

    $times = is_array($campaign->schedule_times ?? null) ? $campaign->schedule_times : [];
    $times = collect($times)
        ->map(fn ($time) => trim((string) $time))
        ->filter(fn ($time) => preg_match('/^\d{2}:\d{2}$/', $time) === 1)
        ->unique()
        ->sort()
        ->values()
        ->all();

    $legacyTrigger = strtolower(trim((string) data_get($automation, 'trigger', '')));
    $legacyTime = trim((string) data_get($automation, 'schedule.time', ''));
    $isLegacySchedule = $times === [] && $legacyTime !== '';

    if ($isLegacySchedule) {
        $times = [$legacyTime];
        $weekdays = [0, 1, 2, 3, 4, 5, 6];
        if ($scheduleMode === null) {
            $scheduleMode = 'indefinite';
            $modeLabel = 'Indefinida';
        }
    }

    $weekdayLabelMap = [
        0 => 'Domingo',
        1 => 'Segunda',
        2 => 'Terça',
        3 => 'Quarta',
        4 => 'Quinta',
        5 => 'Sexta',
        6 => 'Sábado',
    ];

    $weekdaysLabel = collect($weekdays)
        ->map(fn ($day) => $weekdayLabelMap[$day] ?? null)
        ->filter()
        ->implode(', ');

    $timesLabel = $times !== [] ? implode(', ', $times) : '—';

    $intervalLabel = '—';
    if ($startsAt) {
        $startLabel = $startsAt->format('d/m/Y H:i');
        if ($scheduleMode === 'indefinite') {
            $intervalLabel = 'A partir de ' . $startLabel;
        } elseif ($endsAt) {
            $intervalLabel = $startLabel . ' até ' . $endsAt->format('d/m/Y H:i');
        } else {
            $intervalLabel = $startLabel;
        }
    }

    $legacyTriggerLabelMap = [
        'birthday' => 'Aniversário',
        'inactive_patients' => 'Pacientes inativos',
    ];
    $legacyTriggerLabel = $legacyTriggerLabelMap[$legacyTrigger] ?? ($legacyTrigger !== '' ? ucfirst($legacyTrigger) : null);

    $lastAutomationRunAt = $lastAutomationRun?->started_at ?? null;
    $nextAutomationRunAt = $nextAutomationRun ?? null;
@endphp

<div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
    <dl class="grid grid-cols-1 gap-4">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Modo</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $modeLabel }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Intervalo</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $intervalLabel }}</dd>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Dias da semana</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $weekdaysLabel !== '' ? $weekdaysLabel : '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Horários</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $timesLabel }}</dd>
            </div>
        </div>

        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Timezone</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $timezone !== '' ? $timezone : '—' }}</dd>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Última execução</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ $lastAutomationRunAt ? $lastAutomationRunAt->format('d/m/Y H:i') : '—' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Próxima execução prevista</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ $nextAutomationRunAt ? $nextAutomationRunAt->format('d/m/Y H:i') : '—' }}
                </dd>
            </div>
        </div>

        @if ($isLegacySchedule)
            <div class="rounded-md border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-200">
                Registro legado detectado: origem em automação antiga ({{ $legacyTriggerLabel ?? 'trigger desconhecido' }}). Esta campanha passa a usar os campos de programação (`schedule_*`).
            </div>
        @endif
    </dl>
</div>
