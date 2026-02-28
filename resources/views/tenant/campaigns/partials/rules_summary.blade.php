@php
    $rulesSummary = is_array($rulesSummary ?? null) ? $rulesSummary : null;
    $conditions = is_array($rulesSummary['conditions'] ?? null) ? $rulesSummary['conditions'] : [];
@endphp

<div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
    @if (!$rulesSummary || $conditions === [])
        <p class="text-sm text-gray-500 dark:text-gray-400">Nenhuma regra configurada.</p>
    @else
        <div class="mb-3">
            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Logica</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $rulesSummary['logic_label'] ?? ($rulesSummary['logic'] ?? 'AND') }}</dd>
        </div>

        <ul class="space-y-2 text-sm text-gray-800 dark:text-gray-200">
            @foreach ($conditions as $condition)
                <li class="rounded-md border border-gray-200 bg-gray-50 px-3 py-2 dark:border-gray-700 dark:bg-gray-900/30">
                    <span class="font-medium">{{ $condition['field_label'] ?? '-' }}</span>
                    <span class="text-gray-500 dark:text-gray-400">{{ $condition['operator_label'] ?? '-' }}</span>
                    <span>{{ $condition['value_label'] ?? '-' }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</div>

