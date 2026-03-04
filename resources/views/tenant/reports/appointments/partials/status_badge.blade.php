@php
    $statusMap = [
        'scheduled' => ['label' => 'Agendado', 'class' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300'],
        'confirmed' => ['label' => 'Confirmado', 'class' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300'],
        'attended' => ['label' => 'Atendido', 'class' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'],
        'canceled' => ['label' => 'Cancelado', 'class' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'],
        'no_show' => ['label' => 'Nao compareceu', 'class' => 'bg-slate-100 text-slate-800 dark:bg-slate-900/30 dark:text-slate-300'],
    ];

    $entry = $statusMap[$status ?? ''] ?? [
        'label' => $statusLabel ?? ($status ?? 'N/A'),
        'class' => 'bg-slate-100 text-slate-800 dark:bg-slate-900/30 dark:text-slate-300',
    ];
@endphp
<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $entry['class'] }}">
    {{ $statusLabel ?? $entry['label'] }}
</span>
