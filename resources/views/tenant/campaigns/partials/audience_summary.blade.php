@php
    $audience = is_array($audience ?? null) ? $audience : [];
    $source = strtolower(trim((string) data_get($audience, 'source', '')));
    $sourceLabelMap = [
        'patients' => 'Pacientes',
    ];
    $sourceLabel = $sourceLabelMap[$source] ?? ($source !== '' ? ucfirst($source) : '—');

    $requireEmail = (bool) data_get($audience, 'require.email', false);
    $requireWhatsapp = (bool) data_get($audience, 'require.whatsapp', false);

    $filters = data_get($audience, 'filters', []);
    $hasFilters = is_array($filters) && $filters !== [];
    $filtersJson = $hasFilters
        ? json_encode($filters, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
        : null;
@endphp

<div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
    <dl class="grid grid-cols-1 gap-4">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Origem</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $sourceLabel }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Exige Email</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $requireEmail ? 'Sim' : 'Não' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Exige WhatsApp</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $requireWhatsapp ? 'Sim' : 'Não' }}</dd>
            </div>
        </div>

        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Filtros</dt>
            <dd class="mt-1 rounded-md border border-gray-200 bg-gray-50 p-3 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                @if ($hasFilters && $filtersJson)
                    <pre class="whitespace-pre-wrap break-words">{{ $filtersJson }}</pre>
                @else
                    Sem filtros definidos.
                @endif
            </dd>
        </div>
    </dl>
</div>
