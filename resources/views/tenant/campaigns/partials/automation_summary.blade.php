@php
    $automation = is_array($automation ?? null) ? $automation : [];

    $trigger = strtolower(trim((string) data_get($automation, 'trigger', '')));
    $triggerLabelMap = [
        'birthday' => 'Aniversário',
        'inactive_patients' => 'Pacientes inativos',
    ];
    $triggerLabel = $triggerLabelMap[$trigger] ?? ($trigger !== '' ? ucfirst($trigger) : '—');

    $scheduleType = strtolower(trim((string) data_get($automation, 'schedule.type', '')));
    $scheduleTime = trim((string) data_get($automation, 'schedule.time', ''));
    $timezone = trim((string) data_get($automation, 'timezone', ''));
    if ($timezone === '') {
        $timezone = trim((string) ($automationTimezone ?? ''));
    }

    $lastAutomationRunAt = $lastAutomationRun?->started_at ?? null;
    $nextAutomationRunAt = $nextAutomationRun ?? null;

    $extras = $automation;
    unset($extras['trigger'], $extras['schedule'], $extras['timezone'], $extras['version']);
    $extrasJson = $extras !== []
        ? json_encode($extras, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
        : null;
@endphp

<div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
    <dl class="grid grid-cols-1 gap-4">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Trigger</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $triggerLabel }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Frequência</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $scheduleType !== '' ? $scheduleType : '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Horário</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $scheduleTime !== '' ? $scheduleTime : '—' }}</dd>
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

        @if ($extrasJson)
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Dados adicionais</dt>
                <dd class="mt-1 rounded-md border border-gray-200 bg-gray-50 p-3 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                    <pre class="whitespace-pre-wrap break-words">{{ $extrasJson }}</pre>
                </dd>
            </div>
        @endif
    </dl>
</div>
